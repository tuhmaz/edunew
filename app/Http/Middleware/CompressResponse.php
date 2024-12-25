<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class CompressResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldCompress($request)) {
            // تكوين الضغط
            $level = Config::get('app.compression.level', 6);
            $threshold = Config::get('app.compression.threshold', 1024);
            
            ini_set('zlib.output_compression', 'On');
            ini_set('zlib.output_compression_level', $level);
            ini_set('zlib.output_handler', Config::get('app.compression.handler', 'ob_gzhandler'));
            
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            ob_start('ob_gzhandler');
        }

        $response = $next($request);

        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        if (!$this->isCompressibleContent($response)) {
            return $response;
        }

        // إضافة رؤوس التخزين المؤقت
        $this->addCacheHeaders($response);

        // إضافة رؤوس الأمان
        $this->addSecurityHeaders($response);

        // إضافة ETag للتحقق من التغييرات
        $this->addETag($response);

        // تحسين الأداء
        $this->addPerformanceHeaders($response);

        return $response;
    }

    /**
     * تحديد ما إذا كان يجب ضغط الطلب
     */
    protected function shouldCompress(Request $request): bool
    {
        if (!Config::get('app.compression.enabled', true)) {
            return false;
        }

        // تحقق من رؤوس If-None-Match و If-Modified-Since
        if ($this->isNotModified($request)) {
            return false;
        }

        if (!$request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->headers->has('X-No-Compression')) {
            return false;
        }

        $acceptEncoding = $request->header('Accept-Encoding', '');
        return strpos($acceptEncoding, 'gzip') !== false || 
               strpos($acceptEncoding, 'deflate') !== false || 
               strpos($acceptEncoding, 'br') !== false;
    }

    /**
     * تحديد ما إذا كان المحتوى قابل للضغط
     */
    protected function isCompressibleContent(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        
        // تحقق من أنواع المحتوى المسموح بها
        $allowedTypes = Config::get('app.compression.types', []);
        foreach ($allowedTypes as $type) {
            if (strpos($contentType, $type) === 0) {
                $content = $response->getContent();
                // تحقق من الحجم الأدنى
                return strlen($content) >= Config::get('app.compression.threshold', 1024);
            }
        }

        return false;
    }

    /**
     * إضافة رؤوس التخزين المؤقت
     */
    protected function addCacheHeaders(Response $response): void
    {
        $maxAge = Config::get('app.compression.cache_max_age', 86400);
        
        $response->headers->set('Cache-Control', "public, max-age={$maxAge}, must-revalidate, no-transform");
        $response->headers->set('Vary', 'Accept-Encoding, User-Agent');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');
    }

    /**
     * إضافة رؤوس الأمان
     */
    protected function addSecurityHeaders(Response $response): void
    {
        // رؤوس الأمان الأساسية
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Download-Options', 'noopen');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // رؤوس CORS
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // سياسة الأمان
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), screen-wake-lock=(), accelerometer=(), gyroscope=(), magnetometer=(), midi=()');

        // إضافة HSTS إذا كان مُمكّناً وكان الاتصال آمناً
        if ($this->shouldEnableHSTS()) {
            $this->addHSTSHeader($response);
        }
    }

    /**
     * تحقق مما إذا كان يجب تمكين HSTS
     */
    protected function shouldEnableHSTS(): bool
    {
        $config = Config::get('app.compression.security.hsts', []);
        
        return $config['enabled'] ?? false
            && request()->secure();
    }

    /**
     * إضافة رأس HSTS
     */
    protected function addHSTSHeader(Response $response): void
    {
        $config = Config::get('app.compression.security.hsts', []);
        
        $maxAge = $config['max_age'] ?? 31536000;
        $includeSubdomains = $config['include_subdomains'] ?? true;
        $preload = $config['preload'] ?? true;
        
        $header = "max-age={$maxAge}";
        
        if ($includeSubdomains) {
            $header .= '; includeSubDomains';
        }
        
        if ($preload) {
            $header .= '; preload';
        }
        
        $response->headers->set('Strict-Transport-Security', $header);
    }

    /**
     * إضافة ETag للتحقق من التغييرات
     */
    protected function addETag(Response $response): void
    {
        $content = $response->getContent();
        if (!empty($content)) {
            $etag = md5($content);
            $response->headers->set('ETag', '"' . $etag . '"', true);
        }
    }

    /**
     * تحقق مما إذا كان المحتوى لم يتغير
     */
    protected function isNotModified(Request $request): bool
    {
        $ifNoneMatch = $request->header('If-None-Match');
        $ifModifiedSince = $request->header('If-Modified-Since');

        if ($ifNoneMatch || $ifModifiedSince) {
            $etag = md5($request->fullUrl());
            if ($ifNoneMatch === '"' . $etag . '"') {
                return true;
            }

            if ($ifModifiedSince) {
                $lastModified = strtotime($ifModifiedSince);
                if ($lastModified && $lastModified >= time() - Config::get('app.compression.cache_max_age', 86400)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * إضافة رؤوس تحسين الأداء
     */
    protected function addPerformanceHeaders(Response $response): void
    {
        // تمكين ضغط HTTP/2 Server Push
        $response->headers->set('Link', '</>; rel=preload; as=document');
        
        // تحسين الأداء للمتصفحات الحديثة
        if (Config::get('app.compression.enable_brotli', true)) {
            $response->headers->set('Accept-CH', 'Sec-CH-UA, Sec-CH-UA-Mobile, Sec-CH-UA-Platform');
        }
    }
}
