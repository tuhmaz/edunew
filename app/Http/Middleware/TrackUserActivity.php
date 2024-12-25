<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $this->trackActivity();
        return $next($request);
    }

    private function trackActivity()
    {
        $sessionId = Session::getId();
        $timestamp = now();

        if (auth()->guest()) {
            $activeGuests = Cache::get('active_guests', []);
            $activeGuests[$sessionId] = [
                'timestamp' => $timestamp,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];
            Cache::put('active_guests', $activeGuests, now()->addMinutes(5));
        } else {
            $activeUsers = Cache::get('active_users', []);
            $activeUsers[$sessionId] = [
                'id' => auth()->id(),
                'name' => auth()->user()->name,
                'timestamp' => $timestamp,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];
            Cache::put('active_users', $activeUsers, now()->addMinutes(5));
        }

        Cache::increment('page_views');
    }
}
