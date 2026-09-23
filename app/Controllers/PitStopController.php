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
use App\Services\Schema;
use Throwable;

final class PitStopController extends Controller
{
    private const NEARBY_PLACES = 8;

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
            'likedIds' => PortRiderController::likedIdsFor($portRiders, $places),
            'site' => new SiteRepository(),
        ]);
    }

    public function showRegister(Request $request): string
    {
        $member = Auth::user();
        $selected = (string) ($request->input('acara') ?? '');

        // Guests see why they need an account; after signing in or signing up
        // they come straight back here with their chosen stop still selected.
        if ($member === null) {
            Session::put('_intended', '/pit-stop/daftar' . ($selected !== '' ? '?acara=' . rawurlencode($selected) : ''));
        }

        $seo = $this->seo()
            ->setTitle('Pendaftaran Pit Stop')
            ->setDescription('Daftar nombor plat motor anda untuk tempah slot pit stop The Bikers Ranger.')
            ->setCanonical('/pit-stop/daftar');

        return $this->view('pages/pit-stop/register', [
            'seo' => $seo,
            'member' => $member,
            'events' => $member === null ? [] : (new PitStopEventRepository())->openForRegistration(),
            'selected' => $selected,
            'registrations' => $member === null ? [] : (new PitStopRegistrationRepository())->allForUser((int) $member['id']),
            'states' => Config::get('site.states', []),
        ]);
    }

    public function register(Request $request): never
    {
        Auth::requireLogin($request);
        $this->verifyCsrf($request);

        $member = (array) Auth::user();

        $validator = new Validator($_POST, [
            'plate' => 'required|plate|max:20',
            'event' => 'required|max:180',
            'state' => 'in:' . implode(',', array_keys(Config::get('site.states', []))),
            'pdpa' => 'accepted',
        ], [
            'plate' => 'Nombor plat',
            'event' => 'Pit stop',
            'state' => 'Negeri',
            'pdpa' => 'persetujuan pemprosesan data peribadi',
        ]);

        $data = $validator->validated();
        $input = [
            'plate' => $request->input('plate'),
            'event' => $request->input('event'),
            'state' => $request->input('state'),
            'pdpa' => $request->has('pdpa') ? '1' : null,
        ];
        $back = '/pit-stop/daftar' . ($input['event'] !== null ? '?acara=' . rawurlencode($input['event']) : '');

        if (!$validator->passes()) {
            $this->backWithErrors($back, $validator->errors(), $input);
        }

        try {
            $registrationId = (new PitStopRegistrationRepository())->register(
                (string) $data['event'],
                $member,
                normalizePlate((string) $data['plate']),
                $data['state'] ?? ($member['state'] ?? null),
                packIp($request->ip()),
            );
        } catch (ValidationException $exception) {
            $this->backWithErrors($back, $exception->errors, $input);
        }

        $this->sendConfirmation($registrationId, $member);

        Session::flash('_registration_id', $registrationId);
        Response::redirect('/pit-stop/daftar/berjaya');
    }

    public function confirmation(Request $request): string
    {
        Auth::requireLogin($request);

        $registrationId = Session::getFlash('_registration_id');
        $registration = is_int($registrationId)
            ? (new PitStopRegistrationRepository())->findForUser($registrationId, (int) Auth::id())
            : null;

        // Only reachable straight after a booking; a refresh or a shared link goes back to the form.
        if ($registration === null) {
            Response::redirect('/pit-stop/daftar');
        }

        $seo = $this->seo()->setTitle('Pendaftaran Diterima')->noIndex();

        return $this->view('pages/pit-stop/confirmation', ['seo' => $seo, 'registration' => $registration]);
    }

    /** @param array<string,mixed> $member */
    private function sendConfirmation(int $registrationId, array $member): void
    {
        $registration = (new PitStopRegistrationRepository())->findForUser($registrationId, (int) $member['id']);

        if ($registration === null || empty($member['email'])) {
            return;
        }

        $siteName = (string) Config::get('app.name');
        $body = "Hai {$member['name']},\n\n"
            . "Terima kasih! Pendaftaran pit stop anda telah kami terima dan akan disemak.\n\n"
            . "Pit stop: {$registration['title']}\n"
            . 'Tarikh: ' . formatDate((string) $registration['starts_at'], true) . "\n"
            . "Nombor plat: {$registration['plate_no']}\n\n"
            . "Maklumat lokasi dan masa akan dihantar melalui WhatsApp setelah pendaftaran disahkan.\n"
            . "Ingat: jemputan diperlukan, tiada walk-in.\n\n"
            . "— {$siteName}\n";

        // The booking is already saved; a mail hiccup must not turn it into an error page.
        try {
            (new Mailer())->send((string) $member['email'], "Pendaftaran pit stop diterima: {$registration['title']}", $body);
        } catch (Throwable $exception) {
            error_log('Pit stop confirmation email failed: ' . $exception->getMessage());
        }
    }
}
