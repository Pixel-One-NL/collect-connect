<?php

declare(strict_types=1);

namespace App\Support;

use Inertia\Inertia;

/**
 * Flashes a one-time toast notification to the next Inertia response.
 * Rendered globally by `resources/js/components/UI/FlashMessages.jsx`.
 */
class Toast
{
    /**
     * @param  array{label: string, href: string}|null  $action
     */
    public static function success(string $message, ?string $title = null, ?array $action = null): void
    {
        self::flash('success', $message, $title, $action);
    }

    /**
     * @param  array{label: string, href: string}|null  $action
     */
    public static function error(string $message, ?string $title = null, ?array $action = null): void
    {
        self::flash('error', $message, $title, $action);
    }

    /**
     * @param  array{label: string, href: string}|null  $action
     */
    public static function info(string $message, ?string $title = null, ?array $action = null): void
    {
        self::flash('info', $message, $title, $action);
    }

    /**
     * @param  'success'|'error'|'info'  $type
     * @param  array{label: string, href: string}|null  $action
     */
    protected static function flash(string $type, string $message, ?string $title, ?array $action): void
    {
        Inertia::flash('toast', array_filter([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action' => $action,
        ], fn (mixed $value): bool => $value !== null));
    }
}
