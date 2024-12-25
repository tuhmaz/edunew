@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('page-style')
    @vite(['resources/assets/js/security-logs.js'])

@endsection

@section('title', 'تفاصيل السجل الأمني')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />
@endsection

@section('content')
<div class="container-fluid">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">الأمان /</span> تفاصيل السجل
    </h4>

    <div class="card mb-4">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="ri-file-info-line me-2"></i>تفاصيل الحدث
                </h5>
                <div>
                    @if(!$log->is_resolved)
                        <form action="{{ route('security.logs.resolve', $log) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-check-line me-1"></i>تحديد كمحلول
                            </button>
                        </form>
                    @endif

                    <!-- زر حظر IP -->
                    <form action="{{ route('security.logs.block-ip', $log) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            <i class="ri-shield-cross-line me-1"></i>حظر IP
                        </button>
                    </form>

                    <!-- زر إضافة IP موثوق -->
                    <form action="{{ route('security.logs.mark-trusted', $log) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="ri-shield-check-line me-1"></i>IP موثوق
                        </button>
                    </form>

                    <!-- زر حذف السجل -->
                    <form action="{{ route('security.logs.destroy', $log) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="ri-delete-bin-line me-1"></i>حذف
                        </button>
                    </form>

                    <a href="{{ route('security.logs.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>عودة
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">التاريخ والوقت</h6>
                    <p class="text-dark">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">عنوان IP</h6>
                    <p class="text-dark">{{ $log->ip_address }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">نوع الحدث</h6>
                    <p class="text-dark">{{ $log->event_type }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">درجة الخطورة</h6>
                    <span class="badge bg-label-{{ $log->severity === 'danger' ? 'danger' : ($log->severity === 'warning' ? 'warning' : 'info') }}">
                        @if($log->severity === 'danger')
                            <i class="me-1 ri-alarm-warning-line"></i>
                        @elseif($log->severity === 'warning')
                            <i class="me-1 ri-alert-line"></i>
                        @else
                            <i class="me-1 ri-information-line"></i>
                        @endif
                        {{ $log->severity }}
                    </span>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">المستخدم</h6>
                    <p class="text-dark">{{ $log->user ? $log->user->name : 'غير معروف' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">الحالة</h6>
                    @if($log->is_resolved)
                        <span class="badge bg-label-success">تم الحل</span>
                    @else
                        <span class="badge bg-label-warning">قيد المعالجة</span>
                    @endif
                </div>
                <div class="col-12 mb-3">
                    <h6 class="text-dark">الوصف</h6>
                    <p class="text-dark">{{ $log->description }}</p>
                </div>
                <div class="col-12">
                    <h6 class="text-dark">معلومات المتصفح</h6>
                    <pre class="bg-lighter p-3 rounded text-dark">{{ $log->user_agent }}</pre>
                </div>
            </div>
        </div>
    </div>

    @if($log->is_resolved)
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">
                <i class="ri-checkbox-circle-line me-2"></i>معلومات الحل
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">تم الحل بواسطة</h6>
                    <p class="text-dark">{{ $log->resolved_by ? $log->resolved_by->name : 'غير معروف' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <h6 class="text-dark">تاريخ الحل</h6>
                    <p class="text-dark">{{ $log->resolved_at ? $log->resolved_at->format('Y-m-d H:i:s') : 'غير متوفر' }}</p>
                </div>
                @if($log->resolution_notes)
                <div class="col-12">
                    <h6 class="text-dark">ملاحظات الحل</h6>
                    <p class="text-dark">{{ $log->resolution_notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
