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
        $users = new UserRepository();

        if (!isset($errors['email']) && $users->emailExists($email)) {
            $errors['email'] = 'Alamat e-mel ini sudah didaftarkan. Sila log masuk.';
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
                'password' => (string) $data['password'],
                'phone' => normalizePhone((string) $data['phone']),
                'state' => (string) $data['state'],
                'follows_tbr' => $request->has('follows_tbr'),
                'follows_raja_kapcai' => $request->has('follows_raja_kapcai'),
                'whatsapp_opt_in' => $request->has('whatsapp_opt_in'),
                'contest_opt_in' => $request->has('contest_opt_in'),
                'consent_ip' => packIp($request->ip()),
            ]);
        } catch (PDOException $exception) {
            // Two submits racing past emailExists() both hit the unique index.
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->backWithErrors(
                self::SIGNUP_ANCHOR,
                ['email' => 'Alamat e-mel ini sudah didaftarkan. Sila log masuk.'],
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

    public function logout(Request $request): never
    {
        $this->verifyCsrf($request);
        Auth::logout();
        Session::regenerate();

        $this->redirectWithStatus('/', 'Anda telah log keluar.', 'info');
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
