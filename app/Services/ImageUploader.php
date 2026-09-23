<?php

declare(strict_types=1);

namespace App\Services;

use GdImage;
use RuntimeException;

/**
 * Validates an uploaded image and re-encodes it with GD. Re-encoding means the
 * stored file is pixels we produced ourselves: anything smuggled inside the
 * original (scripts, polyglot payloads, EXIF GPS location) is discarded.
 */
final class ImageUploader
{
    public const MAX_BYTES = 2 * 1024 * 1024;
    public const ADMIN_MAX_BYTES = 8 * 1024 * 1024;
    private const MAX_DIMENSION = 6000;
    private const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

    /** @param int $maxBytes members get the default; the admin panel allows larger originals */
    public function __construct(private readonly int $maxBytes = self::MAX_BYTES)
    {
    }

    /**
     * Crops to a centred square, resizes and saves as WebP.
     *
     * @param array<string,mixed> $file one entry from $_FILES
     * @return string path relative to public/uploads, e.g. "avatars/ab12….webp"
     * @throws RuntimeException with a member-facing Malay message
     */
    public function storeSquare(array $file, string $directory, int $size): string
    {
        $image = $this->open($file);

        $width = imagesx($image);
        $height = imagesy($image);
        $side = min($width, $height);

        $square = imagecreatetruecolor($size, $size);
        imagealphablending($square, false);
        imagesavealpha($square, true);
        imagecopyresampled(
            $square,
            $image,
            0,
            0,
            (int) (($width - $side) / 2),
            (int) (($height - $side) / 2),
            $size,
            $size,
            $side,
            $side,
        );

        return $this->save($square, $directory);
    }

    /**
     * Keeps the aspect ratio and only ever scales down, for photos, banners
     * and covers where cropping would lose part of the picture.
     *
     * @param array<string,mixed> $file one entry from $_FILES
     * @return string path relative to public/uploads
     * @throws RuntimeException with a member-facing Malay message
     */
    public function storeResized(array $file, string $directory, int $maxWidth): string
    {
        return $this->saveResized($this->open($file), $directory, $maxWidth);
    }

    /**
     * Same as storeResized() for a file already on the server, such as the
     * launch photos in database/seed-media. Skips the upload checks, so it
     * must only ever be given paths the code chose, never user input.
     *
     * @return string path relative to public/uploads
     */
    public function importResized(string $path, string $directory, int $maxWidth): string
    {
        if (!is_file($path)) {
            throw new RuntimeException("Image not found: {$path}");
        }

        return $this->saveResized($this->decode($path), $directory, $maxWidth);
    }

    /** Deletes a previously stored upload, refusing anything outside uploads/. */
    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $root = realpath(BASE_PATH . '/public/uploads');
        $target = realpath(BASE_PATH . '/public/uploads/' . $relativePath);

        if ($root !== false && $target !== false && str_starts_with($target, $root . DIRECTORY_SEPARATOR) && is_file($target)) {
            unlink($target);
        }
    }

    /** @param array<string,mixed> $file */
    private function open(array $file): GdImage
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        $tooBig = 'Saiz gambar terlalu besar. Maksimum ' . (int) ($this->maxBytes / 1024 / 1024) . 'MB.';

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new RuntimeException($tooBig);
        }

        $path = (string) ($file['tmp_name'] ?? '');

        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($path)) {
            throw new RuntimeException('Gambar gagal dimuat naik. Sila cuba lagi.');
        }

        if (filesize($path) > $this->maxBytes) {
            throw new RuntimeException($tooBig);
        }

        return $this->decode($path);
    }

    private function decode(string $path): GdImage
    {
        // Trust the file's bytes, never the browser-supplied name or type.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $info = getimagesize($path);

        if (!in_array($mime, self::ALLOWED, true) || $info === false) {
            throw new RuntimeException('Format gambar tidak disokong. Sila guna JPG, PNG atau WebP.');
        }

        // A tiny file can declare huge dimensions and exhaust memory when decoded.
        if ($info[0] > self::MAX_DIMENSION || $info[1] > self::MAX_DIMENSION) {
            throw new RuntimeException('Dimensi gambar terlalu besar. Maksimum 6000 x 6000 piksel.');
        }

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
        };

        if (!$image instanceof GdImage) {
            throw new RuntimeException('Gambar tidak dapat dibaca. Sila cuba gambar lain.');
        }

        return $mime === 'image/jpeg' ? $this->applyExifOrientation($image, $path) : $image;
    }

    /** Phone photos are stored sideways with a rotation flag; bake the rotation in. */
    private function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        // Malformed EXIF is common in phone photos and there is no way to
        // pre-check it; a bad block only costs us the rotation, so mute it.
        $exif = @exif_read_data($path);
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        return $rotated instanceof GdImage ? $rotated : $image;
    }

    /** Keeps the aspect ratio and only ever scales down. */
    private function saveResized(GdImage $image, string $directory, int $maxWidth): string
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * $maxWidth / $width);
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            $image = $resized;
        }

        return $this->save($image, $directory);
    }

    private function save(GdImage $image, string $directory): string
    {
        $directory = trim($directory, '/');
        $absoluteDir = BASE_PATH . '/public/uploads/' . $directory;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Gambar tidak dapat disimpan. Sila cuba lagi.');
        }

        $filename = bin2hex(random_bytes(16)) . '.webp';

        if (!imagewebp($image, $absoluteDir . '/' . $filename, 82)) {
            throw new RuntimeException('Gambar tidak dapat disimpan. Sila cuba lagi.');
        }

        return $directory . '/' . $filename;
    }
}
