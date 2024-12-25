@extends('layouts/layoutMaster')

@section('title', __('classes'))

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
@endsection

@push('styles')
<style>
/* Variables */
:root {
    --primary-color: #696cff;
    --success-color: #71dd37;
    --warning-color: #ffab00;
    --danger-color: #ff3e1d;
    --info-color: #03c3ec;
    --dark-color: #233446;
    --light-color: #f5f5f9;
    --border-color: #f0f2f8;
    --card-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
}

/* Card Styles */
.content-card {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid var(--border-color);
    box-shadow: var(--card-shadow);
    transition: all 0.3s ease;
    margin-bottom: 2rem;
}

.content-card:hover {
    box-shadow: 0 4px 12px 0 rgba(67, 89, 113, 0.16);
}

/* Header Styles */
.card-header {
    background: #fff;
    border-bottom: 1px solid var(--border-color);
    padding: 1.5rem;
    border-radius: 0.75rem 0.75rem 0 0;
}

.card-header h5 {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin: 0;
    display: flex;
    align-items: center;
}

.card-header h5 i {
    font-size: 1.5rem;
    margin-right: 0.5rem;
    color: var(--primary-color);
}

/* Button Styles */
.btn-add {
    background: var(--success-color);
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    color: #fff;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.btn-add:hover {
    background: #65c832;
    transform: translateY(-1px);
}

.btn-add i {
    margin-right: 0.5rem;
    font-size: 1.25rem;
}

/* Form Styles */
.filter-form {
    background: var(--light-color);
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin: 1rem 1.5rem;
}

.form-group label {
    font-weight: 500;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.form-select {
    border: 1px solid var(--border-color);
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
    font-size: 0.9375rem;
    transition: all 0.2s ease;
}

.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
}

/* Table Styles */
.table-container {
    padding: 0 1.5rem 1.5rem;
}

.custom-table {
    width: 100%;
    margin-bottom: 0;
    border-radius: 0.5rem;
    overflow: hidden;
}

.custom-table thead th {
    background: var(--dark-color);
    color: #fff;
    font-weight: 500;
    padding: 1rem;
    font-size: 0.9375rem;
    border: none;
}

.custom-table thead th i {
    margin-right: 0.5rem;
    font-size: 1.125rem;
}

.custom-table tbody td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
    color: var(--dark-color);
}

.custom-table tbody tr {
    transition: all 0.2s ease;
}

.custom-table tbody tr:hover {
    background: var(--light-color);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.btn-action {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
}

.btn-edit {
    color: var(--warning-color);
    border: 1px solid var(--warning-color);
    background: transparent;
}

.btn-edit:hover {
    background: var(--warning-color);
    color: #fff;
}

.btn-delete {
    color: var(--danger-color);
    border: 1px solid var(--danger-color);
    background: transparent;
}

.btn-delete:hover {
    background: var(--danger-color);
    color: #fff;
}

.btn-action i {
    font-size: 1rem;
    margin-right: 0.25rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-header {
        padding: 1rem;
    }
    
    .filter-form {
        margin: 1rem;
    }
    
    .table-container {
        padding: 0 1rem 1rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .content-card {
        background: #2b2c40;
        border-color: #444564;
    }
    
    .card-header {
        background: #2b2c40;
        border-color: #444564;
    }
    
    .form-select {
        background-color: #2b2c40;
        border-color: #444564;
        color: #a3a4cc;
    }
    
    .custom-table tbody td {
        color: #a3a4cc;
        border-color: #444564;
    }
    
    .custom-table tbody tr:hover {
        background: #32334a;
    }
}
</style>
@endpush

@section('content')
<div class="content-body">
    <div class="container-fluid">
        <div class="card overflow-hidden">
            <div class="card-header d-flex justify-content-between align-items-center flex-column flex-md-row">
                <h5>
                    <i class="ri-classroom-line me-2"></i>{{ __('classes') }}
                </h5>
                <a href="{{ route('classes.create') }}" class="btn btn-success mt-3 mt-md-0">
                    <i class="ri-add-line me-1"></i>{{ __('add_new_class') }}
                </a>
            </div>

            <form method="GET" action="{{ route('classes.index') }}" class="mb-4 p-3">
                <div class="form-group">
                    <label for="country" class="form-label">{{ __('Select Country') }}</label>
                    <select class="form-select" id="country" name="country" onchange="this.form.submit()">
                        <option value="jordan" {{ $country == 'jordan' ? 'selected' : '' }}>Jordan (Main Database)</option>
                        <option value="saudi" {{ $country == 'saudi' ? 'selected' : '' }}>Saudi Arabia</option>
                        <option value="egypt" {{ $country == 'egypt' ? 'selected' : '' }}>Egypt</option>
                        <option value="palestine" {{ $country == 'palestine' ? 'selected' : '' }}>Palestine</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive text-nowrap">
                <table class="table table-striped table-bordered">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th><i class="ri-book-2-line me-1"></i>{{ __('name') }}</th>
                            <th><i class="ri-honour-line me-1"></i>{{ __('grade_level') }}</th>
                            <th class="text-center"><i class="ri-tools-line me-1"></i>{{ __('actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schoolClasses as $class)
                        <tr>
                            <td>
                                <i class="ri-suitcase-2-line ri-22px text-danger me-2"></i>
                                {{ $class->grade_name }}
                            </td>
                            <td>{{ $class->grade_level }}</td>
                            <td class="text-center">
                                <a href="{{ route('classes.edit', ['class' => $class->id, 'country' => request()->input('country')]) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="ri-pencil-line me-1"></i>{{ __('edit') }}
                                </a>
                                <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this class?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="country" value="{{ request()->input('country') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="ri-delete-bin-7-line me-1"></i>{{ __('delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach((row, index) => {
        row.style.animation = `fadeIn 0.3s ease-out forwards ${index * 0.1}s`;
        row.style.opacity = '0';
    });

    // Enhanced form select
    const countrySelect = document.getElementById('country');
    countrySelect.addEventListener('change', function() {
        this.closest('form').classList.add('submitting');
    });

    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn-action, .btn-add');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Enhance delete confirmation
    const deleteForms = document.querySelectorAll('form[action*="destroy"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('button');
            if (confirm(button.dataset.confirmMessage || 'Are you sure?')) {
                button.disabled = true;
                button.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Deleting...';
                this.submit();
            }
        });
    });
});
</script>
@endpush
