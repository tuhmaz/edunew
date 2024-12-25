<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class SecurityHeaders
{
    /**
     * رؤوس الأمان الافتراضية
     */
    protected $securityHeaders = [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), screen-wake-lock=(), accelerometer=(), gyroscope=(), magnetometer=(), midi=()',
        'Cross-Origin-Embedder-Policy' => 'require-corp',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ];

    /**
     * معالجة الطلب
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // إضافة رؤوس الأمان الأساسية
        foreach ($this->securityHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }

        // تكوين سياسة CSP المحسنة
        $response->headers->set('Content-Security-Policy', $this->getEnhancedCSP());

        // تحسين إعدادات ملفات تعريف الارتباط
        if ($response->headers->has('Set-Cookie')) {
            $cookies = $response->headers->getCookies();
            foreach ($cookies as $cookie) {
                $cookie->setSecure(true);
                $cookie->setHttpOnly(true);
                $cookie->setSameSite('strict');
            }
        }

        // إضافة رؤوس CORS إذا كان ضرورياً
        if ($this->shouldAllowCORS($request)) {
            $frontendUrl = config('app.frontend_url', '*');
            $response->headers->set('Access-Control-Allow-Origin', $frontendUrl);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // إضافة HSTS في بيئة الإنتاج
        if (App::environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // إضافة رؤوس أمان إضافية
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('X-Download-Options', 'noopen');

        return $response;
    }

    /**
     * الحصول على سياسة CSP المحسنة
     */
    protected function getEnhancedCSP(): string
    {
        $policies = [
            "default-src" => ["'self'"],
            "script-src" => ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https:", "*.googleapis.com", "*.gstatic.com", "*.fontawesome.com", "cdn.jsdelivr.net", "cdnjs.cloudflare.com"],
            "style-src" => ["'self'", "'unsafe-inline'", "https:", "*.googleapis.com", "*.gstatic.com", "*.fontawesome.com", "cdn.jsdelivr.net", "cdnjs.cloudflare.com"],
            "img-src" => ["'self'", "data:", "https:", "blob:"],
            "font-src" => ["'self'", "data:", "https:", "*.googleapis.com", "*.gstatic.com", "*.fontawesome.com"],
            "connect-src" => ["'self'", "https:", "wss:"],
            "media-src" => ["'self'", "https:"],
            "object-src" => ["'none'"],
            "child-src" => ["'self'", "blob:"],
            "frame-src" => ["'self'", "https:"],
            "worker-src" => ["'self'", "blob:"],
            "frame-ancestors" => ["'self'"],
            "form-action" => ["'self'"],
            "base-uri" => ["'self'"],
            "manifest-src" => ["'self'"],
            "upgrade-insecure-requests" => true
        ];

        return implode('; ', array_map(function ($key, $values) {
            if ($key === 'upgrade-insecure-requests') {
                return $values === true ? 'upgrade-insecure-requests' : '';
            }
            return $key . ' ' . implode(' ', $values);
        }, array_keys($policies), $policies));
    }

    /**
     * تحديد ما إذا كان يجب السماح بـ CORS للطلب
     */
    protected function shouldAllowCORS(Request $request): bool
    {
        return $request->headers->has('Origin') && 
               $request->headers->get('Origin') !== $request->getSchemeAndHttpHost();
    }
}
