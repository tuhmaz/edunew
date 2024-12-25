<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;

class MonitoringController extends Controller
{
    public function index()
    {
        return view('dashboard.monitoring');
    }

    public function getStats()
    {
        try {
            // تحديث نشاط المستخدم أولاً
            $this->updateUserActivity();

            // جمع الإحصائيات
            $stats = [
                'visitors' => $this->getVisitorStats(),
                'system' => $this->getSystemStats(),
                'locations' => $this->getVisitorLocations(),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على الإحصائيات: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'حدث خطأ أثناء جلب البيانات',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getVisitorStats()
    {
        try {
            // تنظيف الجلسات القديمة
            $this->cleanOldSessions();

            // الحصول على البيانات من الكاش
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $pageViews = Cache::get('page_views', 0);

            // تحليل المتصفحات
            $browsers = [];
            foreach (array_merge($activeUsers, $activeGuests) as $session) {
                if (isset($session['user_agent'])) {
                    $agent = new Agent();
                    $agent->setUserAgent($session['user_agent']);
                    $browser = $agent->browser();
                    $browsers[$browser] = ($browsers[$browser] ?? 0) + 1;
                }
            }

            return [
                'users' => count($activeUsers),
                'guests' => count($activeGuests),
                'total' => count($activeUsers) + count($activeGuests),
                'pageViews' => $pageViews,
                'browsers' => $browsers
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في getVisitorStats: ' . $e->getMessage());
            return [
                'users' => 0,
                'guests' => 0,
                'total' => 0,
                'pageViews' => 0,
                'browsers' => []
            ];
        }
    }

    private function getSystemStats()
    {
        try {
            return [
                'cpu_usage' => $this->getCpuUsage(),
                'memory_usage' => $this->getMemoryUsage(),
                'cache_status' => Cache::get('cache_status', 'متصل'),
                'last_update' => now()->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            Log::error('خطأ في getSystemStats: ' . $e->getMessage());
            return [
                'cpu_usage' => 0,
                'memory_usage' => 0,
                'cache_status' => 'غير متصل',
                'last_update' => now()->format('Y-m-d H:i:s')
            ];
        }
    }

    private function getCpuUsage()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = "wmic cpu get loadpercentage";
                $output = shell_exec($cmd);
                if (preg_match("/\d+/", $output, $matches)) {
                    return (int)$matches[0];
                }
            } else {
                $load = sys_getloadavg();
                return (int)($load[0] * 100 / processor_count());
            }
        } catch (\Exception $e) {
            Log::error('خطأ في getCpuUsage: ' . $e->getMessage());
        }
        return 0;
    }

    private function getMemoryUsage()
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = "wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value";
                $output = shell_exec($cmd);
                
                preg_match("/FreePhysicalMemory=(\d+)/", $output, $free);
                preg_match("/TotalVisibleMemorySize=(\d+)/", $output, $total);
                
                if (isset($free[1]) && isset($total[1])) {
                    $used = $total[1] - $free[1];
                    return round(($used / $total[1]) * 100);
                }
            } else {
                $free = shell_exec('free');
                $free = (string)trim($free);
                $free_arr = explode("\n", $free);
                $mem = explode(" ", $free_arr[1]);
                $mem = array_filter($mem);
                $mem = array_merge($mem);
                return round($mem[2]/$mem[1]*100);
            }
        } catch (\Exception $e) {
            Log::error('خطأ في getMemoryUsage: ' . $e->getMessage());
        }
        return 0;
    }

    private function getVisitorLocations()
    {
        try {
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $locations = [];

            foreach (array_merge($activeUsers, $activeGuests) as $session) {
                $ip = $session['ip_address'] ?? '127.0.0.1';
                $location = $this->getLocationFromIP($ip);
                $locations[$location] = ($locations[$location] ?? 0) + 1;
            }

            return $locations;
        } catch (\Exception $e) {
            Log::error('خطأ في getVisitorLocations: ' . $e->getMessage());
            return ['غير معروف' => 1];
        }
    }

    private function getLocationFromIP($ip)
    {
        // هذه نسخة مبسطة للعرض. في الإنتاج، استخدم خدمة GeoIP
        return 'المملكة العربية السعودية';
    }

    private function updateUserActivity()
    {
        try {
            $sessionId = Session::getId();
            $userId = auth()->id();
            $userAgent = request()->header('User-Agent');
            $ipAddress = request()->ip();

            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);

            $currentTime = now();
            $activity = [
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'last_activity' => $currentTime->timestamp,
            ];

            if ($userId) {
                $activeUsers[$sessionId] = $activity;
                Cache::put('active_users', $activeUsers, now()->addMinutes(5));
            } else {
                $activeGuests[$sessionId] = $activity;
                Cache::put('active_guests', $activeGuests, now()->addMinutes(5));
            }

            // تحديث عداد مشاهدات الصفحة
            $pageViews = Cache::get('page_views', 0);
            Cache::put('page_views', $pageViews + 1, now()->addDay());
        } catch (\Exception $e) {
            Log::error('خطأ في updateUserActivity: ' . $e->getMessage());
        }
    }

    private function cleanOldSessions()
    {
        try {
            $activeUsers = Cache::get('active_users', []);
            $activeGuests = Cache::get('active_guests', []);
            $currentTime = now()->timestamp;
            $timeout = 5 * 60; // 5 minutes in seconds

            foreach ($activeUsers as $sessionId => $data) {
                if (!isset($data['last_activity']) || ($currentTime - $data['last_activity']) > $timeout) {
                    unset($activeUsers[$sessionId]);
                }
            }

            foreach ($activeGuests as $sessionId => $data) {
                if (!isset($data['last_activity']) || ($currentTime - $data['last_activity']) > $timeout) {
                    unset($activeGuests[$sessionId]);
                }
            }

            Cache::put('active_users', $activeUsers, now()->addMinutes(5));
            Cache::put('active_guests', $activeGuests, now()->addMinutes(5));
        } catch (\Exception $e) {
            Log::error('خطأ في cleanOldSessions: ' . $e->getMessage());
        }
    }

    public function clearCache()
    {
        try {
            Cache::flush();
            return response()->json([
                'success' => true, 
                'message' => 'تم تنظيف ذاكرة التخزين المؤقت بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في تنظيف الكاش: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تنظيف ذاكرة التخزين المؤقت'
            ], 500);
        }
    }
}
