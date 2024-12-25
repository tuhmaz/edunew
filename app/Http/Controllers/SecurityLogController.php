<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlockedIp;
use App\Models\SecurityLog;
use App\Models\TrustedIp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecurityLogController extends Controller
{
    public function index()
    {
        $logs = SecurityLog::with('user')
            ->recent()
            ->paginate(15);

        return view('dashboard.security.logs.index', compact('logs'));
    }

    public function show(SecurityLog $log)
    {
        return view('dashboard.security.logs.show', compact('log'));
    }

    public function resolve(SecurityLog $log, Request $request)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000'
        ]);

        $log->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
            'resolution_notes' => $request->resolution_notes
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث حالة السجل بنجاح'
            ]);
        }

        return redirect()->route('security.logs.index')
            ->with('success', 'تم تحديث حالة السجل بنجاح');
    }

    public function filter(Request $request)
    {
        $request->validate([
            'severity' => 'nullable|in:info,warning,danger',
            'event_type' => 'nullable|in:login_failed,suspicious_activity,blocked_access',
            'ip_address' => 'nullable|ip',
            'unresolved' => 'nullable|boolean'
        ]);

        $query = SecurityLog::with('user');

        if ($request->filled('severity')) {
            $query->bySeverity($request->severity);
        }

        if ($request->filled('event_type')) {
            $query->byEventType($request->event_type);
        }

        if ($request->filled('ip_address')) {
            $query->byIpAddress($request->ip_address);
        }

        if ($request->boolean('unresolved')) {
            $query->unresolved();
        }

        $logs = $query->recent()->paginate(15);

        return view('dashboard.security.logs.index', compact('logs'));
    }

    public function export()
    {
        $logs = SecurityLog::with('user')->get();
        
        // تنسيق البيانات للتصدير
        $exportData = $logs->map(function ($log) {
            return [
                'ID' => $log->id,
                'التاريخ' => $log->created_at->format('Y-m-d H:i:s'),
                'عنوان IP' => $log->ip_address,
                'نوع الحدث' => $log->event_type,
                'الوصف' => $log->description,
                'المستخدم' => $log->user ? $log->user->name : 'غير معروف',
                'درجة الخطورة' => $log->severity,
                'تم الحل' => $log->is_resolved ? 'نعم' : 'لا',
            ];
        });

        return response()->json($exportData);
    }

    public function destroy(SecurityLog $log)
    {
        $log->delete();
        return redirect()->route('security.logs.index')
            ->with('success', 'تم حذف السجل بنجاح');
    }

    /**
     * حذف مجموعة من السجلات المحددة
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'selected_logs' => 'required|array',
            'selected_logs.*' => 'exists:security_logs,id'
        ]);

        SecurityLog::whereIn('id', $request->selected_logs)->delete();

        return redirect()->back()->with('success', 'تم حذف السجلات المحددة بنجاح');
    }

    public function blockIp(SecurityLog $log)
    {
        // التحقق من عدم وجود IP في القائمة السوداء مسبقاً
        if (BlockedIp::where('ip_address', $log->ip_address)->exists()) {
            return redirect()->back()->with('error', 'عنوان IP محظور بالفعل');
        }

        // التحقق من عدم وجود IP في القائمة البيضاء
        if (TrustedIp::where('ip_address', $log->ip_address)->exists()) {
            return redirect()->back()->with('error', 'لا يمكن حظر عنوان IP موثوق');
        }

        $blockedIp = new BlockedIp([
            'ip_address' => $log->ip_address,
            'reason' => 'تم الحظر من خلال سجل الأمان',
            'blocked_at' => now(),
            'blocked_by' => auth()->id()
        ]);
        $blockedIp->save();

        return redirect()->back()->with('success', 'تم حظر عنوان IP بنجاح');
    }

    public function markAsTrusted(SecurityLog $log)
    {
        // التحقق من عدم وجود IP في القائمة البيضاء مسبقاً
        if (TrustedIp::where('ip_address', $log->ip_address)->exists()) {
            return redirect()->back()->with('error', 'عنوان IP موثوق بالفعل');
        }

        // التحقق من عدم وجود IP في القائمة السوداء
        if (BlockedIp::where('ip_address', $log->ip_address)->exists()) {
            return redirect()->back()->with('error', 'لا يمكن الوثوق بعنوان IP محظور');
        }

        $trustedIp = new TrustedIp([
            'ip_address' => $log->ip_address,
            'reason' => 'تمت إضافته كموثوق من خلال سجل الأمان',
            'added_at' => now(),
            'added_by' => auth()->id()
        ]);
        $trustedIp->save();

        return redirect()->back()->with('success', 'تم إضافة عنوان IP إلى القائمة الموثوقة');
    }
}
