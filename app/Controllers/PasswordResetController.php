<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;
use App\Repositories\PasswordResetRepository;
use App\Repositories\UserRepository;
use App\Services\Mailer;
use App\Services\RateLimiter;

final class PasswordResetController extends Controller
{
    /** Shown whether or not the address belongs to a member. */
    private const SENT_MESSAGE = 'Jika e-mel tersebut berdaftar, pautan untuk menetapkan semula kata laluan telah dihantar. '
        . 'Sila semak peti masuk (dan folder spam) anda.';

    public function showRequest(Request $request): string
    {
        Auth::requireGuest();

        $seo = $this->seo()->setTitle('Lupa Kata Laluan')->noIndex();

        return $this->view('pages/auth/forgot-password', ['seo' => $seo, 'hidePromos' => true]);
    }

    public function sendLink(Request $request): never
    {
        $this->verifyCsrf($request);

        $validator = new Validator($_POST, ['email' => 'required|email|max:190'], ['email' => 'Alamat e-mel']);

        if (!$validator->passes()) {
            $this->backWithErrors('/lupa-kata-laluan', $validator->errors(), ['email' => $request->input('email')]);
        }

        $email = strtolower((string) $validator->validated()['email']);
        $limiter = new RateLimiter();
        $byIp = RateLimiter::bucket('reset-ip', $request->ip());
        $byEmail = RateLimiter::bucket('reset-email', $email);

        // Over the limit we still show the neutral message; we just don't send.
        if (!$limiter->tooManyAttempts($byIp, 5, 3600) && !$limiter->tooManyAttempts($byEmail, 3, 3600)) {
            $limiter->hit($byIp);
            $limiter->hit($byEmail);
            $this->sendResetEmail($email);
        }

        $this->redirectWithStatus('/lupa-kata-laluan', self::SENT_MESSAGE);
    }

    public function showReset(Request $request): string
    {
        Auth::requireGuest();

        $token = (string) $request->routeParam('token');
        $valid = (new PasswordResetRepository())->findValidUserId($token) !== null;

        $seo = $this->seo()->setTitle('Tetapkan Semula Kata Laluan')->noIndex();

        return $this->view('pages/auth/reset-password', [
            'seo' => $seo,
            'hidePromos' => true,
            'token' => $token,
            'valid' => $valid,
        ]);
    }

    public function reset(Request $request): never
    {
        $this->verifyCsrf($request);

        $token = (string) $request->routeParam('token');
        $path = '/reset-kata-laluan/' . $token;
        $resets = new PasswordResetRepository();
        $userId = $resets->findValidUserId($token);

        if ($userId === null) {
            $this->redirectWithStatus('/lupa-kata-laluan', 'Pautan telah tamat tempoh atau sudah digunakan. Sila minta pautan baharu.', 'error');
        }

        $validator = new Validator($_POST, ['password' => 'required|min:8|max:72'], ['password' => 'Kata laluan baharu']);

        if (!$validator->passes()) {
            $this->backWithErrors($path, $validator->errors());
        }

        if (!$resets->consume($token)) {
            $this->redirectWithStatus('/lupa-kata-laluan', 'Pautan telah tamat tempoh atau sudah digunakan. Sila minta pautan baharu.', 'error');
        }

        (new UserRepository())->updatePassword($userId, $request->raw('password'));

        $this->redirectWithStatus('/log-masuk', 'Kata laluan anda telah dikemas kini. Sila log masuk.');
    }

    private function sendResetEmail(string $email): void
    {
        $user = (new UserRepository())->findByEmail($email);

        if ($user === null || $user['status'] !== 'active') {
            return;
        }

        $token = (new PasswordResetRepository())->create((int) $user['id']);
        $link = url('/reset-kata-laluan/' . $token);
        $minutes = PasswordResetRepository::LIFETIME_MINUTES;
        $siteName = (string) Config::get('app.name');

        $body = "Hai {$user['name']},\n\n"
            . "Kami menerima permintaan untuk menetapkan semula kata laluan akaun {$siteName} anda.\n\n"
            . "Klik pautan di bawah untuk memilih kata laluan baharu (sah selama {$minutes} minit):\n"
            . "{$link}\n\n"
            . "Jika anda tidak membuat permintaan ini, abaikan e-mel ini. Kata laluan anda tidak akan berubah.\n\n"
            . "— {$siteName}\n";

        (new Mailer())->send($email, "Tetapkan semula kata laluan {$siteName}", $body);
    }
}
