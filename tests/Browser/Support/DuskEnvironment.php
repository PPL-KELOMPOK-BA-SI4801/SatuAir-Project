<?php

namespace Tests\Browser\Support;

use RuntimeException;

final class DuskEnvironment
{
    public static function guard(): void
    {
        if (! app()->environment('dusk') || getenv('DUSK_TESTING') !== '1'
            || config('database.default') !== 'mysql'
            || config('database.connections.mysql.database') !== 'satuair_dusk_jira'
            || ! in_array(config('database.connections.mysql.host'), ['127.0.0.1', 'localhost'], true)
            || config('database.connections.mysql.url')) {
            throw new RuntimeException('Dusk hanya boleh memakai MySQL lokal satuair_dusk_jira. Jalankan scripts/dusk.ps1.');
        }
    }

    public static function faultPath(): string
    {
        return storage_path('framework/testing/dusk/query-fault.txt');
    }

    public static function clearFault(): void
    {
        if (is_file(self::faultPath())) unlink(self::faultPath());
    }

    public static function failNextQuery(string $operation): void
    {
        self::guard();
        file_put_contents(self::faultPath(), $operation);
    }
}
