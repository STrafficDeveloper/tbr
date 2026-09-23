<?php

declare(strict_types=1);

namespace App\Admin;

use App\Core\Validator;
use App\Services\ImageUploader;
use RuntimeException;

/**
 * Turns a submitted admin form into column => value pairs for a Resource:
 * validates, converts types, generates slugs and stores uploaded images.
 */
final class FormHandler
{
    /** @var list<string> files written during this request, removed again if the save fails */
    private array $newFiles = [];

    /** @var list<string> files replaced or cleared, deleted only once the save succeeds */
    private array $replacedFiles = [];

    public function __construct(
        private readonly ResourceRepository $repository = new ResourceRepository(),
        private readonly ImageUploader $uploader = new ImageUploader(ImageUploader::ADMIN_MAX_BYTES),
    ) {
    }

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $files
     * @param array<string,mixed>|null $existing the row being edited, null when creating
     * @return array{data:array<string,mixed>,errors:array<string,string>}
     */
    public function handle(Resource $resource, array $post, array $files, ?array $existing, ?int $parentId): array
    {
        $rules = [];
        $labels = [];

        foreach ($resource->fields as $field) {
            $rules[$field->name] = $field->rules($parentId);
            $labels[$field->name] = $field->label;
        }

        $validator = new Validator($post, array_filter($rules), $labels);
        $errors = $validator->errors();
        $data = [];

        foreach ($resource->fields as $field) {
            if (isset($errors[$field->name])) {
                continue;
            }

            $raw = $post[$field->name] ?? null;
            $value = is_string($raw) ? trim($raw) : null;
            $value = $value === '' ? null : $value;

            switch ($field->type) {
                case 'checkbox':
                    $data[$field->name] = isset($post[$field->name]) ? 1 : 0;
                    break;

                case 'image':
                    $this->handleImage($field, $resource, $files, $post, $existing, $data, $errors);
                    break;

                case 'datetime':
                    $data[$field->name] = $value === null ? null : date('Y-m-d H:i:s', (int) strtotime(str_replace('T', ' ', $value)));
                    break;

                case 'number':
                    // Blank falls back to the field's default (e.g. sort order 0 for a NOT NULL column).
                    $data[$field->name] = $value === null ? $field->default : (int) $value;
                    break;

                case 'youtube':
                    $id = $value === null ? null : self::youtubeId($value);
                    if ($value !== null && $id === null) {
                        $errors[$field->name] = 'Pautan YouTube tidak dikenali. Tampal pautan video atau ID 11 aksara.';
                        break;
                    }
                    $data[$field->name] = $id;
                    break;

                case 'slug':
                    $source = $field->slugFrom !== null ? (string) ($post[$field->slugFrom] ?? '') : '';
                    $slug = $value ?? slugify($source);
                    if ($slug === '') {
                        // If the title itself is missing, that error already says it all.
                        if ($field->slugFrom === null || !isset($errors[$field->slugFrom])) {
                            $errors[$field->name] = 'Slug URL diperlukan.';
                        }
                        break;
                    }
                    $data[$field->name] = $value === null
                        ? $this->uniqueSlug($resource, $slug, $existing['id'] ?? null)
                        : $slug;
                    if ($value !== null && $this->repository->slugTaken($resource, $slug, $existing['id'] ?? null)) {
                        $errors[$field->name] = 'Slug ini sudah digunakan. Pilih slug lain.';
                    }
                    break;

                default:
                    $data[$field->name] = $value ?? $field->default;
            }
        }

        if ($errors !== []) {
            $this->discardNewFiles();
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /** Call after the row is saved: the images it no longer uses can go now. */
    public function commit(): void
    {
        foreach ($this->replacedFiles as $path) {
            $this->uploader->delete($path);
        }

        $this->replacedFiles = [];
        $this->newFiles = [];
    }

    /** Call if saving failed after handle() succeeded, so no orphan uploads stay behind. */
    public function discardNewFiles(): void
    {
        foreach ($this->newFiles as $path) {
            $this->uploader->delete($path);
        }

        $this->newFiles = [];
    }

    /** Accepts a watch, share, shorts or embed link, or a bare 11-character id. */
    public static function youtubeId(string $input): ?string
    {
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input) === 1) {
            return $input;
        }

        $pattern = '#(?:youtube\.com/(?:watch\?(?:.*&)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})#';

        return preg_match($pattern, $input, $match) === 1 ? $match[1] : null;
    }

    /**
     * @param array<string,mixed> $files
     * @param array<string,mixed> $post
     * @param array<string,mixed>|null $existing
     * @param array<string,mixed> $data
     * @param array<string,string> $errors
     */
    private function handleImage(Field $field, Resource $resource, array $files, array $post, ?array $existing, array &$data, array &$errors): void
    {
        $file = $files[$field->name] ?? null;
        $current = $existing[$field->name] ?? null;
        $hasUpload = is_array($file) && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if ($hasUpload) {
            try {
                $path = $this->uploader->storeResized($file, str_replace('_', '-', $resource->table), $field->imageWidth);
            } catch (RuntimeException $exception) {
                $errors[$field->name] = $exception->getMessage();

                return;
            }

            $this->newFiles[] = $path;
            $data[$field->name] = $path;

            if (!empty($current)) {
                $this->replacedFiles[] = (string) $current;
            }

            return;
        }

        if (isset($post['remove_' . $field->name]) && !empty($current)) {
            if ($field->required) {
                $errors[$field->name] = "{$field->label} diperlukan.";

                return;
            }

            $data[$field->name] = null;
            $this->replacedFiles[] = (string) $current;

            return;
        }

        if ($field->required && empty($current)) {
            $errors[$field->name] = "{$field->label} diperlukan.";
        }
    }

    private function uniqueSlug(Resource $resource, string $base, ?int $exceptId): string
    {
        $base = substr($base, 0, 180);
        $slug = $base;

        for ($n = 2; $this->repository->slugTaken($resource, $slug, $exceptId); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }
}
