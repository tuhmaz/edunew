@php
$configData = Helper::appClasses();
@endphp

@extends('layouts.layoutMaster')

@section('title', 'العناوين الموثوقة')

@section('content')
<div class="container-fluid">
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">الأمان /</span> العناوين الموثوقة
    </h4>

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">
                <i class="ri-shield-check-line me-2"></i>قائمة العناوين الموثوقة
            </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table border-top">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>عنوان IP</th>
                        <th>سبب الإضافة</th>
                        <th>تاريخ الإضافة</th>
                        <th>تمت الإضافة بواسطة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="text-dark">
                    @forelse($trustedIps as $ip)
                        <tr>
                            <td>{{ $ip->ip_address }}</td>
                            <td>{{ $ip->reason }}</td>
                            <td>{{ $ip->added_at->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $ip->addedBy->name }}</td>
                            <td>
                                <form action="{{ route('security.trusted-ips.destroy', $ip) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من إزالة هذا العنوان من القائمة الموثوقة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">لا توجد عناوين IP موثوقة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center mt-3">
                {{ $trustedIps->links('components.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
@endsection
