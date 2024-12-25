@extends('layouts.layoutMaster')

@section('title', 'سجلات الأمان')

@section('page-style')
    @vite(['resources/assets/js/security-logs.js'])

@endsection

@section('content')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/@form-validation/umd/styles/index.min.css')}}" />

<div class="container-fluid">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">الأمان /</span> سجلات الأمان
    </h4>

    <!-- فلتر البحث -->
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0"><i class="ri-filter-line me-2"></i>تصفية النتائج</h5>
        </div>
        <div class="card-body">
            <x-filters.security-logs />
        </div>
    </div>

    <!-- جدول السجلات -->
    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="ri-table-line me-2"></i>سجلات الأمان</h5>

            <!-- الأزرار الخاصة بتحديد الكل وحذف السجلات -->
            <div>
                <button id="bulk-delete-btn" class="btn btn-danger d-none" onclick="submitBulkDelete()">
                    <i class="ri-delete-bin-line me-1"></i> حذف السجلات المحددة
                </button>
                <button id="toggle-select-all-btn" class="btn btn-secondary">
                    <i class="ri-checkbox-multiple-line me-1"></i> تحديد الكل
                </button>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <form id="bulk-delete-form" method="POST" action="{{ route('security.logs.bulk-destroy') }}">
                @csrf
                <table class="table border-top">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all-logs">
                                </div>
                            </th>
                            <th>التاريخ</th>
                            <th>عنوان IP</th>
                            <th>نوع الحدث</th>
                            <th>الوصف</th>
                            <th>المستخدم</th>
                            <th>درجة الخطورة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <x-tables.security-log-row :log="$log" />
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">لا توجد سجلات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $logs->links('components.pagination.custom') }}
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection
