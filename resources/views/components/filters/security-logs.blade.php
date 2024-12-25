<form action="{{ route('security.logs.filter') }}" method="GET" class="row g-3">
    <div class="col-md-3">
        <label class="form-label text-dark">درجة الخطورة</label>
        <select name="severity" class="form-select">
            <option value="">الكل</option>
            <option value="info" @selected(request('severity') === 'info')>معلومات</option>
            <option value="warning" @selected(request('severity') === 'warning')>تحذير</option>
            <option value="danger" @selected(request('severity') === 'danger')>خطر</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label text-dark">نوع الحدث</label>
        <select name="event_type" class="form-select">
            <option value="">الكل</option>
            <option value="login_failed" @selected(request('event_type') === 'login_failed')>فشل تسجيل الدخول</option>
            <option value="suspicious_activity" @selected(request('event_type') === 'suspicious_activity')>نشاط مشبوه</option>
            <option value="blocked_access" @selected(request('event_type') === 'blocked_access')>محاولة وصول محظورة</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label text-dark">عنوان IP</label>
        <input type="text" name="ip_address" class="form-control" value="{{ request('ip_address') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="unresolved" value="1" @checked(request('unresolved'))>
            <label class="form-check-label text-dark">غير محلول فقط</label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary me-2">
            <i class="ri-search-line me-1"></i>تصفية
        </button>
        <a href="{{ route('security.logs.export') }}" class="btn btn-outline-secondary">
            <i class="ri-download-2-line me-1"></i>تصدير
        </a>
    </div>
</form>
