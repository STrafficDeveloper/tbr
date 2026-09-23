<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    /** @param array<string,string> $prefixes namespace prefix => base directory */
    public function __construct(private array $prefixes)
    {
    }

    public function register(): void
    {
        spl_autoload_register($this->load(...));
    }

    private function load(string $class): void
    {
        foreach ($this->prefixes as $prefix => $baseDir) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relative = substr($class, strlen($prefix));
            $file = rtrim($baseDir, '/') . '/' . str_replace('\\', '/', $relative) . '.php';

            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
}
