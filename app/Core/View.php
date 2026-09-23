<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    /** Renders a page template inside the base layout. */
    public static function render(string $template, array $data = [], string $layout = 'layouts/base'): string
    {
        $data['seo'] ??= new Seo();
        $content = self::capture($template, $data);

        return self::capture($layout, $data + ['content' => $content]);
    }

    /** Renders a partial or component on its own, with no layout. */
    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data);
    }

    private static function capture(string $template, array $data): string
    {
        $file = BASE_PATH . '/views/' . $template . '.php';

        if (!is_file($file)) {
            throw new RuntimeException('View not found: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
