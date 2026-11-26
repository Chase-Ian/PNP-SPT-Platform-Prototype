<?php

namespace App\Support;

/**
 * Upload size limits. The app has its own limit per kind of file, but PHP
 * (upload_max_filesize, post_max_size) may accept less: a bigger file never
 * reaches the app's checks. So the limit shown to people and checked in the
 * browser is whichever is smaller.
 */
final class UploadLimits
{
    /** Largest upload the server accepts, in KB. */
    public static function serverKb(): int
    {
        return min(self::iniKb('upload_max_filesize'), self::iniKb('post_max_size'));
    }

    /** The app's limit for a kind of file (KB), capped by what the server accepts. */
    public static function kb(int $appKb): int
    {
        return min($appKb, self::serverKb());
    }

    /** The same limit in whole MB, for messages and the browser check. */
    public static function mb(int $appKb): int
    {
        return max(1, intdiv(self::kb($appKb), 1024));
    }

    /** Message for a file the server refused before the app could check it. */
    public static function tooLargeMessage(): string
    {
        $mb = max(1, intdiv(self::serverKb(), 1024));

        return "That file is too large to upload. Files can be up to {$mb} MB.";
    }

    private static function iniKb(string $key): int
    {
        $value = trim((string) ini_get($key));
        if ($value === '' || $value === '0' || $value === '-1') {
            return PHP_INT_MAX; // no limit set
        }

        $number = (float) $value;

        return (int) match (strtoupper(substr($value, -1))) {
            'G' => $number * 1024 * 1024,
            'M' => $number * 1024,
            'K' => $number,
            default => $number / 1024,
        };
    }
}
