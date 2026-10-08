<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Repositories\PitStopEventRepository;
use App\Repositories\PitStopRegistrationRepository;
use App\Repositories\PortRiderRepository;
use App\Repositories\SiteRepository;
use App\Services\Mailer;
use App\Services\RateLimiter;
use App\Services\Schema;
use Throwable;

final class PitStopController extends Controller
{
    private const NEARBY_PLACES = 8;
    private const BOOKINGS_PER_IP_PER_HOUR = 10;

    /** "Stop & Tarikh": the tour schedule the footer links to. */
    public function index(Request $request): string
    {
        $events = (new PitStopEventRepository())->upcoming(100);

        $seo = $this->seo()
            ->setTitle('Stop & Tarikh Pit Stop')
            ->setDescription('Jadual pit stop The Bikers Ranger seluruh Malaysia: tarikh, lokasi dan pendaftaran slot.')
            ->setCanonical('/pit-stop')
            ->addJsonLd(Schema::breadcrumbs([['Home', '/'], ['Stop & Tarikh', '/pit-stop']]));

        foreach ($events as $event) {
            $seo->addJsonLd(Schema::event($event));
        }

        return $this->view('pages/pit-stop/index', ['seo' => $seo, 'events' => $events]);
    }

    public function show(Request $request): string
    {
        $event = (new PitStopEventRepository())->findPublicBySlug((string) $request->routeParam('slug'));

        if ($event === null) {
            Response::notFound();
        }

        $states = Config::get('site.states', []);
        $stateName = $states[$event['state']] ?? $event['state'];
        $portRiders = new PortRiderRepository();
        $places = $portRiders->search($event['state'], null, self::NEARBY_PLACES);

        $seo = $this->seo()
            ->setTitle("{$event['title']}: " . formatDate($event['starts_at']))
            ->setDescription(
                "Pit stop The Bikers Ranger di {$event['location_name']}, {$stateName} pada "
                . formatDate($event['starts_at'], true) . '. Bukan race, bukan rally: ini gathering. Daftar slot anda sekarang.',
            )
            ->setCanonical('/pit-stop/' . $event['slug'])
            ->addJsonLd(Schema::event($event))
            ->addJsonLd(Schema::breadcrumbs([
                ['Home', '/'],
                ['Stop & Tarikh', '/pit-stop'],
                [(string) $event['title'], '/pit-stop/' . $event['slug']],
            ]));

        if (!empty($event['banner_image'])) {
            $seo->setImage(uploaded($event['banner_image']));
        }

        return $this->view('pages/pit-stop/show', [
            'seo' => $seo,
            'event' => $event,
            'stateName' => $stateName,
            'isOpen' => PitStopEventRepository::isOpen($event),
            'places' => $places,
            'site' => new SiteRepository(),
        ]);
    }

    public function showRegister(Request $request): string
    {
        $member = Auth::user();

        $seo = $this->seo()
            ->setTitle('Pendaftaran Pit Stop')
            ->setDescription('Isi maklumat anda dan nombor plat motor untuk tempah slot pit stop The Bikers Ranger.')
            ->setCanonical('/pit-stop/daftar');

        return $this->view('pages/pit-stop/register', [
            'seo' => $seo,
            'member' => $member,
            'events' => (new PitStopEventRepository())->openForRegistration(),
            'selected' => (string) ($request->input('acara') ?? ''),
            'registrations' => $member === null ? [] : (new PitStopRegistrationRepository())->allForUser((int) $member['id']),
            'states' => Config::get('site.states', []),
        ]);
    }

    /**
     * Open to everyone: guests type their details; signed-in members get them
     * pre-filled and their booking is linked to their account.
     */
    public function register(Request $request): never
    {
        $this->verifyCsrf($request);

        $selected = (string) ($request->input('event') ?? '');
        $back = '/pit-stop/daftar' . ($selected !== '' ? '?acara=' . rawurlencode($selected) : '');

        // Bots fill every field, including this one the form never shows.
        if ($request->input('website') !== null) {
            Response::redirect($back);
        }

        $limiter = new RateLimiter();
        $bucket = RateLimiter::bucket('pitstop', $request->ip());

        if ($limiter->tooManyAttempts($bucket, self::BOOKINGS_PER_IP_PER_HOUR, 3600)) {
            $this->redirectWithStatus($back, 'Terlalu banyak pendaftaran dari rangkaian ini. Sila cuba lagi dalam masa sejam.', 'error');
        }

        $validator = new Validator($_POST, [
            'name' => 'required|max:120',
            'phone' => 'required|phone',
            'email' => 'required|email|max:190',
            'plate' => 'required|plate|max:20',
            'event' => 'required|max:180',
            'state' => 'in:' . implode(',', array_keys(Config::get('site.states', []))),
            'pdpa' => 'accepted',
        ], [
            'name' => 'Nama penuh',
            'phone' => 'Nombor telefon',
            'email' => 'Alamat e-mel',
            'plate' => 'Nombor plat',
            'event' => 'Pit stop',
            'state' => 'Negeri',
            'pdpa' => 'persetujuan pemprosesan data peribadi',
        ]);

        $data = $validator->validated();
        $input = [];
        foreach (['name', 'phone', 'email', 'plate', 'event', 'state'] as $field) {
            $input[$field] = $request->input($field);
        }
        $input['pdpa'] = $request->has('pdpa') ? '1' : null;

        if (!$validator->passes()) {
            $this->backWithErrors($back, $validator->errors(), $input);
        }

        $limiter->hit($bucket);
        $memberId = Auth::id();

        try {
            $registrationId = (new PitStopRegistrationRepository())->register(
                (string) $data['event'],
                [
                    'user_id' => $memberId,
                    'name' => preg_replace('/\s+/', ' ', (string) $data['name']) ?? '',
                    'phone' => normalizePhone((string) $data['phone']),
                    'email' => strtolower((string) $data['email']),
                ],
                normalizePlate((string) $data['plate']),
                $data['state'] ?? null,
                packIp($request->ip()),
            );
        } catch (ValidationException $exception) {
            $this->backWithErrors($back, $exception->errors, $input);
        }

        $this->sendConfirmation($registrationId);

        Session::flash('_registration_id', $registrationId);
        Response::redirect('/pit-stop/daftar/berjaya');
    }

    public function confirmation(Request $request): string
    {
        $registrationId = Session::getFlash('_registration_id');
        $registration = is_int($registrationId)
            ? (new PitStopRegistrationRepository())->findForConfirmation($registrationId)
            : null;

        // Only reachable straight after a booking (the id lives in this
        // browser's session); a refresh or a shared link goes back to the form.
        if ($registration === null) {
            Response::redirect('/pit-stop/daftar');
        }

        $seo = $this->seo()->setTitle('Pendaftaran Pit Stop Berjaya')->noIndex();

        return $this->view('pages/pit-stop/confirmation', [
            'seo' => $seo,
            'registration' => $registration,
            'directions' => directionLinks($registration),
        ]);
    }

    private function sendConfirmation(int $registrationId): void
    {
        $registration = (new PitStopRegistrationRepository())->findForConfirmation($registrationId);

        if ($registration === null || empty($registration['email'])) {
            return;
        }

        $siteName = (string) Config::get('app.name');
        $body = "Hai {$registration['name']},\n\n"
            . "Terima kasih! Pendaftaran pit stop anda telah kami terima.\n\n"
            . 'Nombor pendaftaran: ' . self::reference((int) $registration['id']) . "\n"
            . "Pit stop: {$registration['title']}\n"
            . 'Tarikh: ' . formatDate((string) $registration['starts_at'], true) . "\n"
            . "Lokasi: {$registration['location_name']}\n"
            . "Nombor plat: {$registration['plate_no']}\n\n"
            . "Ingat: jemputan diperlukan, tiada walk-in. Tunjukkan e-mel ini semasa tiba.\n\n"
            . "— {$siteName}\n";

        // The booking is already saved; a mail hiccup must not turn it into an error page.
        try {
            (new Mailer())->send((string) $registration['email'], "Pendaftaran pit stop berjaya: {$registration['title']}", $body);
        } catch (Throwable $exception) {
            error_log('Pit stop confirmation email failed: ' . $exception->getMessage());
        }
    }

    /** The number riders quote at the pit stop, e.g. TBR-000042. */
    public static function reference(int $registrationId): string
    {
        return sprintf('TBR-%06d', $registrationId);
    }
}
