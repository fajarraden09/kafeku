<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias middleware Anda di sini
        $middleware->alias([
            'owner' => \App\Http\Middleware\OwnerMiddleware::class,
            // Daftarkan alias lain di sini jika ada
            // 'nama_panggilan' => \App\Http\Middleware\NamaMiddlewareLain::class,
        ]);

        // Anda juga bisa mengubah middleware group di sini jika perlu
        // $middleware->group('web', [ ... ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
