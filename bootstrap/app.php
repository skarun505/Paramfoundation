<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ScannerMiddleware;
use App\Http\Middleware\CaptureUtm;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Capture UTM params on every web request (first-touch attribution)
        $middleware->web(append: [
            CaptureUtm::class,
        ]);

        // Named middleware aliases
        $middleware->alias([
            'admin'   => AdminMiddleware::class,
            'scanner' => ScannerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

