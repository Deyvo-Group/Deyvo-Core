<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Illuminate\Support\Facades\Session;

final class Flash
{
    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function info(string $message): void
    {
        self::add('info', $message);
    }

    public static function warning(string $message): void
    {
        self::add('warning', $message);
    }

    public static function error(string $message): void
    {
        self::add('error', $message);
    }

    private static function add(string $type, string $message): void
    {
        $key = 'deyvo.flash.'.$type;
        $messages = Session::get($key, []);
        $messages = is_array($messages) ? $messages : [];

        Session::flash($key, [...$messages, $message]);
    }
}
