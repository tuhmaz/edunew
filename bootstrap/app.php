<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\UpdateUserLastActivity;
use App\Http\Middleware\SwitchDatabase;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\CheckApiAccess;
use App\Http\Middleware\ApiRateLimiter;
use App\Http\Middleware\PerformanceOptimizer;
use App\Http\Middleware\CompressResponse;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    // Web Middleware
    $middleware->web(append: [
      LocaleMiddleware::class,
      UpdateUserLastActivity::class,
      SwitchDatabase::class,
      SecurityHeaders::class,
      PerformanceOptimizer::class,
      CompressResponse::class, // إضافة middleware الضغط
    ]);

    // API Middleware
    $middleware->append(CheckApiAccess::class);
    
    // Register Rate Limiter Middleware
    $middleware->append(ApiRateLimiter::class);
  })
  ->withExceptions(function (Exceptions $exceptions) {
  })->create();
