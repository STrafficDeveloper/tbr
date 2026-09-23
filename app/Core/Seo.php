<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Collects per-page metadata that views/partials/meta.php renders into <head>.
 */
final class Seo
{
    private string $title = '';
    private string $description = '';
    private ?string $canonical = null;
    private ?string $image = null;
    private string $robots = 'index, follow';

    /** @var list<array<string,mixed>> */
    private array $jsonLd = [];

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setCanonical(string $path): self
    {
        $this->canonical = url($path);

        return $this;
    }

    public function setImage(string $path): self
    {
        $this->image = str_starts_with($path, 'http') ? $path : url($path);

        return $this;
    }

    public function noIndex(): self
    {
        $this->robots = 'noindex, nofollow';

        return $this;
    }

    /** @param array<string,mixed> $schema */
    public function addJsonLd(array $schema): self
    {
        $this->jsonLd[] = $schema;

        return $this;
    }

    public function title(): string
    {
        $siteName = (string) Config::get('app.name');

        return $this->title === '' ? $siteName : $this->title . ' | ' . $siteName;
    }

    public function description(): string
    {
        return $this->description === ''
            ? (string) Config::get('app.default_description')
            : $this->description;
    }

    public function canonical(): string
    {
        return $this->canonical ?? url((new Request())->path());
    }

    public function image(): string
    {
        return $this->image ?? url('/assets/img/og-default.jpg');
    }

    public function robots(): string
    {
        return $this->robots;
    }

    /** @return list<array<string,mixed>> */
    public function jsonLd(): array
    {
        return $this->jsonLd;
    }
}
