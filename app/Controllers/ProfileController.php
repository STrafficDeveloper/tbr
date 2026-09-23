<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\UserRepository;
use App\Services\ImageUploader;
use PDOException;
use RuntimeException;

final class ProfileController extends Controller
{
    private const AVATAR_SIZE = 400;

    public function show(Request $request): string
    {
        Auth::requireLogin($request);

        $seo = $this->seo()->setTitle('Tetapan Akaun')->noIndex();

        return $this->view('pages/profile/settings', [
            'seo' => $seo,
            'hidePromos' => true,
            'member' => (new UserRepository())->find((int) Auth::id()),
            'states' => Config::get('site.states', []),
        ]);
    }

    public function update(Request $request): never
    {
        Auth::requireLogin($request);
        $this->verifyCsrf($request);

        $users = new UserRepository();
        $member = $users->find((int) Auth::id());

        $validator = new Validator($_POST, [
            'name' => 'required|max:120',
            'email' => 'required|email|max:190',
            'phone' => 'required|phone',
            'state' => 'required|in:' . implode(',', array_keys(Config::get('site.states', []))),
            'new_password' => 'min:8|max:72',
        ], [
            'name' => 'Nama penuh',
            'email' => 'E-mel',
            'phone' => 'Nombor telefon',
            'state' => 'Negeri',
            'new_password' => 'Kata laluan baharu',
        ]);

        $data = $validator->validated();
        $errors = $validator->errors();
        $email = strtolower((string) $data['email']);
        $phone = normalizePhone((string) $data['phone']);
        $newPassword = $request->raw('new_password');

        if (!isset($errors['email']) && $users->emailExists($email, (int) $member['id'])) {
            $errors['email'] = 'Alamat e-mel ini digunakan oleh akaun lain.';
        }

        if (!isset($errors['phone']) && $users->phoneExists($phone, (int) $member['id'])) {
            $errors['phone'] = 'Nombor telefon ini digunakan oleh akaun lain.';
        }

        // Changing the sign-in details must prove it's really the member,
        // not someone at an unlocked phone or a hijacked session.
        $changingCredentials = $email !== $member['email'] || $newPassword !== '';

        if ($changingCredentials && !password_verify($request->raw('current_password'), (string) $member['password_hash'])) {
            $errors['current_password'] = 'Sila masukkan kata laluan semasa yang betul untuk menukar e-mel atau kata laluan.';
        }

        $input = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'state' => $request->input('state'),
        ];

        if ($errors !== []) {
            $this->backWithErrors('/tetapan', $errors, $input);
        }

        try {
            $users->updateProfile((int) $member['id'], [
                'name' => preg_replace('/\s+/', ' ', (string) $data['name']) ?? '',
                'email' => $email,
                'phone' => $phone,
                'state' => (string) $data['state'],
            ]);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $this->backWithErrors('/tetapan', ['email' => 'E-mel atau nombor telefon ini digunakan oleh akaun lain.'], $input);
        }

        if ($newPassword !== '') {
            $users->updatePassword((int) $member['id'], $newPassword);
            Session::regenerate();
            Auth::refreshFingerprint((int) $member['id']);
        }

        $this->redirectWithStatus('/tetapan', 'Tetapan anda telah disimpan.');
    }

    public function updateAvatar(Request $request): never
    {
        Auth::requireLogin($request);

        // Over post_max_size PHP drops the whole body, CSRF token included,
        // so say "too big" instead of the misleading "session expired".
        if ($_POST === [] && $_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->redirectWithStatus('/tetapan', 'Saiz gambar terlalu besar. Maksimum 2MB.', 'error');
        }

        $this->verifyCsrf($request);

        $file = $request->file('avatar');

        if ($file === null) {
            $this->redirectWithStatus('/tetapan', 'Sila pilih gambar untuk dimuat naik.', 'error');
        }

        $uploader = new ImageUploader();
        $users = new UserRepository();
        $member = $users->find((int) Auth::id());

        try {
            $path = $uploader->storeSquare($file, 'avatars', self::AVATAR_SIZE);
        } catch (RuntimeException $exception) {
            $this->redirectWithStatus('/tetapan', $exception->getMessage(), 'error');
        }

        $users->updateAvatar((int) $member['id'], $path);
        $uploader->delete($member['avatar_path'] ?? null);

        $this->redirectWithStatus('/tetapan', 'Gambar profil dikemas kini.');
    }
}
