<tr>
    <td>
        <div class="form-check">
            <input class="form-check-input log-checkbox" type="checkbox" name="selected_logs[]" value="{{ $log->id }}">
        </div>
    </td>
    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
    <td>{{ $log->ip_address }}</td>
    <td>{{ $log->event_type }}</td>
    <td>{{ \Illuminate\Support\Str::limit($log->description, 50) }}</td>
    <td>{{ $log->user ? $log->user->name : 'غير معروف' }}</td>
    <td>
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
    </td>
    <td>
        @if($log->is_resolved)
            <span class="badge bg-label-success">تم الحل</span>
        @else
            <form action="{{ route('security.logs.resolve', $log) }}" method="POST" class="d-inline resolve-form">
                @csrf
                <input type="hidden" name="resolution_notes" value="تم حل المشكلة">
                <button type="submit" class="btn btn-sm btn-outline-success">
                    <i class="ri-checkbox-circle-line me-1"></i>تحديد كمحلول
                </button>
            </form>
        @endif
    </td>
    <td>
        <a href="{{ route('security.logs.show', $log) }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill">
            <i class="ri-eye-line"></i>
        </a>
    </td>
</tr>
