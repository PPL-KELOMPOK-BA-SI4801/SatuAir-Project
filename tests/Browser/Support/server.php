<?php

// Router server Dusk saja; tidak didaftarkan pada aplikasi kerja / production.
$root = dirname(__DIR__, 3);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
error_log('DUSK '.($_SERVER['REQUEST_METHOD'] ?? 'GET').' '.$_SERVER['REQUEST_URI']);
$asset = realpath($root.'/public'.rawurldecode($path));
if ($path !== '/' && $asset && str_starts_with($asset, realpath($root.'/public').DIRECTORY_SEPARATOR) && is_file($asset)) return false;
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\BootProviders::class, function ($app) {
    \Tests\Browser\Support\DuskEnvironment::guard();
    header('X-SatuAir-Dusk-Database: satuair_dusk_jira');
    config(['session.files' => storage_path('framework/testing/dusk/sessions')]);
    \Illuminate\Support\Facades\DB::connection()->beforeExecuting(function ($sql) {
        $file = \Tests\Browser\Support\DuskEnvironment::faultPath();
        if (! is_file($file)) return;
        $operation = trim(file_get_contents($file));
        $path = request()->path();
        $matches = ($operation === 'users-filter' && $path === 'bendahara/pengguna'
                && str_contains(strtolower($sql), 'count(*)') && str_contains($sql, '`users`'))
            || ($operation === 'customer-save' && request()->isMethod('PUT')
                && preg_match('#^bendahara/pengguna/\d+/profil$#', $path)
                && str_starts_with(strtolower($sql), 'update `users`'));
        if ($matches) {
            unlink($file);
            throw new \Illuminate\Database\QueryException('mysql', $sql, [], new \PDOException('Simulasi koneksi database gagal - server Dusk saja.'));
        }
    });
});
$app->handleRequest(\Illuminate\Http\Request::capture());
