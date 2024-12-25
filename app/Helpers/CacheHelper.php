<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheHelper
{
    /**
     * الحصول على عنصر من التخزين المؤقت مع إمكانية إعادة التحميل
     *
     * @param string $key
     * @param \Closure $callback
     * @param int $ttl
     * @return mixed
     */
    public static function remember($key, \Closure $callback, $ttl = 3600)
    {
        try {
            return Cache::remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache error: ' . $e->getMessage());
            return $callback();
        }
    }

    /**
     * تخزين عنصر في التخزين المؤقت مع التعامل مع الأخطاء
     *
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     */
    public static function put($key, $value, $ttl = 3600)
    {
        try {
            return Cache::put($key, $value, $ttl);
        } catch (\Exception $e) {
            Log::error('Cache error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف عنصر من التخزين المؤقت
     *
     * @param string $key
     * @return bool
     */
    public static function forget($key)
    {
        try {
            return Cache::forget($key);
        } catch (\Exception $e) {
            Log::error('Cache error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * تنظيف التخزين المؤقت بالكامل
     *
     * @return bool
     */
    public static function flush()
    {
        try {
            return Cache::flush();
        } catch (\Exception $e) {
            Log::error('Cache error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على عنصر من التخزين المؤقت مع دعم التجزئة
     *
     * @param string $key
     * @param array $tags
     * @param \Closure $callback
     * @param int $ttl
     * @return mixed
     */
    public static function rememberWithTags($key, array $tags, \Closure $callback, $ttl = 3600)
    {
        try {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        } catch (\Exception $e) {
            Log::error('Cache error: ' . $e->getMessage());
            return $callback();
        }
    }
}
