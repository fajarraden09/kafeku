<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Di sini kita menentukan koneksi database default yang akan digunakan.
    | PASTIKAN NILAI KEDUA ADALAH 'mysql'.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Di sini kita mendefinisikan semua koneksi database.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        // ===== PASTIKAN BLOK DI BAWAH INI SAMA PERSIS =====
        'mysql' => [
            'driver' => 'mysql',
            // Baris 'url' ini akan memprioritaskan variabel dari Railway
            'url' => env('DATABASE_URL'), 
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        // ===== BATAS BLOK YANG PERLU DIPASTIKAN =====

        // ... (koneksi lain seperti pgsql, sqlsrv) ...

    ],

    // ... (sisa file konfigurasi) ...

];