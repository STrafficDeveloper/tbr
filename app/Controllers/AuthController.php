<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\UserRepository;
use App\Services\LoginThrottle;
use App\Services\RateLimiter;
use PDOException;

final class AuthController extends Controller
{
    private const SIGNUP_ANCHOR = '/#daftar';
    private const SIGNUPS_PER_IP_PER_HOUR = 5;

    /** The design has no separate sign-up page: the form lives in the home hero. */
    public function showSignup(Request $request): never
    {
        Response::redirect(self::SIGNUP_ANCHOR, 301);
    }

    public function register(Request $request): never
    {
        $this->verifyCsrf($request);

        // Bots fill every field, including this one the design never shows.
        if ($request->input('website') !== null) {
            Response::redirect('/');
        }

        $limiter = new RateLimiter();
        $bucket = RateLimiter::bucket('signup', $request->ip());

        if ($limiter->tooManyAttempts($bucket, self::SIGNUPS_PER_IP_PER_HOUR, 3600)) {
            $this->backToSignup('error', 'Terlalu banyak cubaan pendaftaran. Sila cuba lagi dalam masa sejam.');
        }

        $validator = new Validator($_POST, [
            'name' => 'required|max:120',
            'phone' => 'required|phone',
            'email' => 'required|email|max:190',
            'password' => 'required|min:8|max:72',
            'state' => 'required|in:' . implode(',', array_keys(Config::get('site.states', []))),
        ], [
            'name' => 'Nama penuh',
            'phone' => 'Nombor telefon',
            'email' => 'Alamat e-mel',
            'password' => 'Kata laluan',
            'state' => 'Negeri',
        ]);

        $data = $validator->validated();
        $errors = $validator->errors();
        $email = strtolower((string) $data['email']);
        $phone = normalizePhone((string) $data['phone']);
        $users = new UserRepository();

        if (!isset($errors['email']) && $users->emailExists($email)) {
            $errors['email'] = 'Alamat e-mel ini sudah didaftarkan. Sila log masuk.';
        }

        if (!isset($errors['phone']) && $users->phoneExists($phone)) {
            $errors['phone'] = 'Nombor telefon ini sudah didaftarkan. Sila log masuk.';
        }

        $input = $this->safeInput($request);

        if ($errors !== []) {
            $this->backWithErrors(self::SIGNUP_ANCHOR, $errors, $input);
        }

        $limiter->hit($bucket);

        try {
            $userId = $users->createMember([
                'name' => preg_replace('/\s+/', ' ', (string) $data['name']) ?? '',
                'email' => $email,
                'password' => $request->raw('password'),
                'phone' => $phone,
                'state' => (string) $data['state'],
                'follows_tbr' => $request->has('follows_tbr'),
                'follows_raja_kapcai' => $request->has('follows_raja_kapcai'),
                'whatsapp_opt_in' => $request->has('whatsapp_opt_in'),
                'contest_opt_in' => $request->has('contest_opt_in'),
                'consent_ip' => packIp($request->ip()),
            ]);
        } catch (PDOException $exception) {
            // Two submits racing past the exists() checks both hit a unique index.
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->backWithErrors(
                self::SIGNUP_ANCHOR,
                ['email' => 'Alamat e-mel atau nombor telefon ini sudah didaftarkan. Sila log masuk.'],
                $input,
            );
        }

        Auth::login($userId);

        $this->backToSignup('success', 'Tahniah, pendaftaran berjaya! Jemputan pit stop akan dihantar melalui WhatsApp.');
    }

    /**
     * The browser lands on #daftar, below where page-level messages sit, so
     * sign-up messages get their own key and render inside the form card.
     */
    private function backToSignup(string $type, string $message): never
    {
        Session::flash('_signup_status', ['type' => $type, 'message' => $message]);
        Response::redirect(self::SIGNUP_ANCHOR);
    }

    public function showLogin(Request $request): string
    {
        Auth::requireGuest();

        $seo = $this->seo()
            ->setTitle('Log Masuk')
            ->setDescription('Log masuk ke akaun ahli The Bikers Ranger untuk tempah slot pit stop.')
            ->noIndex();

        return $this->view('pages/auth/login', ['seo' => $seo, 'hidePromos' => true]);
    }

    public function login(Request $request): never
    {
        $this->verifyCsrf($request);

        $raw = (string) ($request->input('login') ?? '');
        $password = $request->raw('password');
        $identifier = str_contains($raw, '@') ? strtolower($raw) : normalizePhone($raw);

        if ($raw === '' || $password === '') {
            $this->backWithErrors('/log-masuk', ['login' => 'Sila isi e-mel atau nombor telefon dan kata laluan.'], ['login' => $raw]);
        }

        $users = new UserRepository();
        $user = $users->findByLogin($identifier);

        // Count failures against the account, not the text typed, so switching
        // between a member's email and phone number doesn't earn extra guesses.
        $throttleKey = $user !== null ? 'user:' . $user['id'] : $identifier;
        $throttle = new LoginThrottle();

        if ($throttle->isLocked($throttleKey, $request->ip())) {
            $this->backWithErrors('/log-masuk', [
                'login' => 'Terlalu banyak cubaan log masuk. Sila cuba lagi dalam ' . $throttle->windowMinutes() . ' minit.',
            ], ['login' => $raw]);
        }

        // Always run one bcrypt check, so an unknown account takes as long to
        // reject as a wrong password and response time can't reveal who is a member.
        $validPassword = password_verify($password, (string) ($user['password_hash'] ?? $this->timingHash()));
        $success = $user !== null && $validPassword && $user['status'] === 'active';

        $throttle->record($throttleKey, $request->ip(), $success);

        if (!$success) {
            $this->backWithErrors('/log-masuk', [
                'login' => 'E-mel/nombor telefon atau kata laluan tidak tepat.',
            ], ['login' => $raw]);
        }

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
            $users->updatePassword((int) $user['id'], $password);
        }

        Auth::login((int) $user['id']);

        $this->redirectWithStatus(Auth::pullIntendedUrl('/'), 'Selamat kembali, ' . $user['name'] . '!');
    }

    public function logout(Request $request): never
    {
        $this->verifyCsrf($request);
        Auth::logout();
        Session::regenerate();

        $this->redirectWithStatus('/', 'Anda telah log keluar.', 'info');
    }

    /**
     * A throwaway hash at this server's default cost, so rejecting an unknown
     * account does the same bcrypt work as rejecting a wrong password. Made
     * once and cached; remade if a PHP upgrade changes the default cost.
     */
    private function timingHash(): string
    {
        $file = BASE_PATH . '/storage/timing-hash.txt';
        $hash = is_file($file) ? trim((string) file_get_contents($file)) : '';

        if ($hash === '' || password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            file_put_contents($file, $hash, LOCK_EX);
        }

        return $hash;
    }

    /** @return array<string,string|null> everything worth refilling, never the password */
    private function safeInput(Request $request): array
    {
        $input = [];

        foreach (['name', 'phone', 'email', 'state'] as $field) {
            $input[$field] = $request->input($field);
        }

        foreach (['follows_tbr', 'follows_raja_kapcai', 'whatsapp_opt_in', 'contest_opt_in'] as $checkbox) {
            $input[$checkbox] = $request->has($checkbox) ? '1' : null;
        }

        return $input;
    }
}
