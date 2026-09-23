<?php

declare(strict_types=1);

namespace App\Admin;

use Closure;

/**
 * One editable column on an admin form.
 *
 * Types: text, textarea, email, tel, number, decimal, date, datetime, select,
 * checkbox, url (http/https only), link (URL or /site-path), image, slug,
 * youtube (accepts a full YouTube link and stores the video id).
 */
final class Field
{
    /**
     * @param array<string,string>|Closure(?int):array<string,string>|null $options select choices,
     *        or a closure given the parent record id (e.g. a contest's own prizes)
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type = 'text',
        public readonly bool $required = false,
        public readonly array|Closure|null $options = null,
        public readonly ?string $hint = null,
        public readonly int $maxLength = 255,
        public readonly ?string $slugFrom = null,
        public readonly int $imageWidth = 1600,
        public readonly mixed $default = null,
    ) {
    }

    /** @return array<string,string> */
    public function options(?int $parentId = null): array
    {
        if ($this->options instanceof Closure) {
            return ($this->options)($parentId);
        }

        return $this->options ?? [];
    }

    /** Validator rules derived from the type, so definitions stay declarative. */
    public function rules(?int $parentId = null): string
    {
        $rules = $this->required && !in_array($this->type, ['checkbox', 'image', 'slug'], true) ? ['required'] : [];

        $rules[] = match ($this->type) {
            'email' => 'email|max:' . $this->maxLength,
            'tel' => 'phone',
            'number' => 'integer',
            'decimal' => 'numeric',
            'date' => 'date',
            'datetime' => 'datetime',
            'url' => 'url|max:500',
            'link' => 'link|max:500',
            'slug' => 'slug|max:' . $this->maxLength,
            'select' => 'in:' . implode(',', array_keys($this->options($parentId))),
            'textarea' => 'max:' . max($this->maxLength, 20000),
            'checkbox', 'image', 'youtube' => '',
            default => 'max:' . $this->maxLength,
        };

        return implode('|', array_filter($rules));
    }
}
