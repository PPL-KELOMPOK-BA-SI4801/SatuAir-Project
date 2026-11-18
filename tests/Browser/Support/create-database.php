<?php

// Tidak memuat .env dan tidak menerima nama database bebas.
if (getenv('DUSK_TESTING') !== '1' || getenv('DB_DATABASE') !== 'satuair_dusk_jira'
    || ! in_array(getenv('DB_HOST'), ['127.0.0.1', 'localhost'], true)) {
    throw new RuntimeException('Konfigurasi database Dusk tidak aman.');
}
$pdo = new PDO('mysql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE DATABASE IF NOT EXISTS `satuair_dusk_jira` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
echo "Database uji: satuair_dusk_jira (terpisah dari satuair).\n";
