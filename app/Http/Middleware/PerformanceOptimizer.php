<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PerformanceOptimizer
{
    /**
     * معالجة الطلب
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // تطبيق التحسينات فقط على استجابات HTML
        if ($this->isHtmlResponse($response)) {
            $this->optimizeResponse($response);
        }

        return $response;
    }

    /**
     * التحقق مما إذا كانت الاستجابة HTML
     */
    protected function isHtmlResponse($response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        return strpos($contentType, 'text/html') !== false;
    }

    /**
     * تحسين الاستجابة
     */
    protected function optimizeResponse($response): void
    {
        $content = $response->getContent();

        // إضافة preload للموارد المهمة
        if (Config::get('performance.optimization.preload_resources')) {
            $this->addPreloadHeaders($response);
        }

        // تطبيق التحميل الكسول للصور
        if (Config::get('performance.optimization.lazy_loading')) {
            $content = $this->addLazyLoading($content);
        }

        // تحسين HTML
        $content = $this->optimizeHtml($content);

        $response->setContent($content);
    }

    /**
     * إضافة preload headers
     */
    protected function addPreloadHeaders($response): void
    {
        // Preload الخطوط المهمة
    //    $response->headers->set('Link', '</fonts/your-main-font.woff2>; rel=preload; as=font; type=font/woff2; crossorigin', false);

        // Preload CSS الأساسي
      //  $response->headers->set('Link', '</css/app.css>; rel=preload; as=style', false);

        // Preload JavaScript الأساسي
      //  $response->headers->set('Link', '</js/app.js>; rel=preload; as=script', false);
    }

    /**
     * إضافة التحميل الكسول للصور
     */
    protected function addLazyLoading(string $content): string
    {
        return preg_replace(
            '/<img((?!loading=)[^>]*)>/i',
            '<img$1 loading="lazy">',
            $content
        );
    }

    /**
     * تحسين HTML
     */
    protected function optimizeHtml(string $content): string
    {
        // إزالة التعليقات غير الضرورية
        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);

        // إزالة المسافات الزائدة
        $content = preg_replace('/\s+/', ' ', $content);

        // تحسين العلامات meta
        $content = str_replace(
            '<meta name="viewport"',
            '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"',
            $content
        );

        return $content;
    }
}
