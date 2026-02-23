<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Excluir rutas de API de la verificación CSRF
        $middleware->validateCsrfTokens(except: [
            '/products/store', 
            '/products/get',
            '/api/*', // Excluir todas las rutas de API
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
