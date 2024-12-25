@extends('layouts.layoutMaster')

@section('title', 'لوحة المراقبة')

@section('vendor-style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .apexcharts-canvas {
            margin: 0 auto;
        }
        .card {
            margin-bottom: 1.5rem;
        }
        .loading-indicator {
            text-align: center;
            padding: 1rem;
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">لوحة المراقبة</h5>
                <div>
                    <span id="last-update" class="text-muted ms-3"></span>
                    <button id="clear-cache-btn" class="btn btn-primary btn-sm">
                        <i class="bx bx-refresh me-1"></i>
                        تنظيف الكاش
                    </button>
                    <div id="loading-indicator" style="display: none;">
                        <i class="bx bx-loader bx-spin"></i>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- إحصائيات الزوار -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>المستخدمين النشطين</h5>
                                <h2 id="online-users-count">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>الزوار</h5>
                                <h2 id="online-guests-count">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>إجمالي المتصلين</h5>
                                <h2 id="total-online-count">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5>مشاهدات الصفحة</h5>
                                <h2 id="page-views-count">0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <!-- مخطط CPU -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>استخدام CPU</h5>
                                <div id="cpu-chart"></div>
                            </div>
                        </div>
                    </div>
                    <!-- مخطط الذاكرة -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>استخدام الذاكرة</h5>
                                <div id="memory-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-4">
                    <!-- مخطط المتصفحات -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>إحصائيات المتصفحات</h5>
                                <div id="browsers-chart"></div>
                            </div>
                        </div>
                    </div>
                    <!-- مخطط المواقع -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5>مواقع الزوار</h5>
                                <div id="locations-chart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // تكوين المخططات
    const charts = {
        cpuChart: new ApexCharts(document.querySelector("#cpu-chart"), {
            series: [0],
            chart: {
                height: 200,
                type: 'radialBar',
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#e7e7e7",
                        strokeWidth: '97%',
                        margin: 5
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            offsetY: -2,
                            fontSize: '22px',
                            formatter: function(val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            colors: ['#00E396']
        }),
        memoryChart: new ApexCharts(document.querySelector("#memory-chart"), {
            series: [0],
            chart: {
                height: 200,
                type: 'radialBar',
                sparkline: {
                    enabled: true
                }
            },
            plotOptions: {
                radialBar: {
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: "#e7e7e7",
                        strokeWidth: '97%',
                        margin: 5
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            offsetY: -2,
                            fontSize: '22px',
                            formatter: function(val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            colors: ['#008FFB']
        }),
        browsersChart: new ApexCharts(document.querySelector("#browsers-chart"), {
            series: [],
            chart: {
                type: 'donut',
                height: 300
            },
            labels: [],
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0']
        }),
        locationsChart: new ApexCharts(document.querySelector("#locations-chart"), {
            series: [],
            chart: {
                type: 'donut',
                height: 300
            },
            labels: [],
            colors: ['#775DD0', '#FF4560', '#FEB019', '#00E396', '#008FFB']
        })
    };

    // تهيئة المخططات
    Object.values(charts).forEach(chart => chart.render());

    // تحديث البيانات
    function updateStats(data) {
        // تحديث إحصائيات الزوار
        document.getElementById('online-users-count').textContent = data.visitors.users || 0;
        document.getElementById('online-guests-count').textContent = data.visitors.guests || 0;
        document.getElementById('total-online-count').textContent = data.visitors.total || 0;
        document.getElementById('page-views-count').textContent = data.visitors.pageViews || 0;

        // تحديث مخططات النظام
        charts.cpuChart.updateSeries([data.system.cpu_usage || 0]);
        charts.memoryChart.updateSeries([data.system.memory_usage || 0]);

        // تحديث مخطط المتصفحات
        if (data.visitors.browsers) {
            const browsers = Object.entries(data.visitors.browsers);
            charts.browsersChart.updateOptions({
                labels: browsers.map(([browser]) => browser),
                series: browsers.map(([, count]) => count)
            });
        }

        // تحديث مخطط المواقع
        if (data.locations) {
            const locations = Object.entries(data.locations);
            charts.locationsChart.updateOptions({
                labels: locations.map(([country]) => country),
                series: locations.map(([, count]) => count)
            });
        }

        // تحديث وقت آخر تحديث
        document.getElementById('last-update').textContent = 
            'آخر تحديث: ' + new Date(data.timestamp).toLocaleTimeString('ar-SA');
    }

    function fetchStats() {
        $.ajax({
            url: "{{ route('dashboard.monitoring.stats') }}",
            method: 'GET',
            success: function(response) {
                updateStats(response);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching stats:', error);
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("error_occurred") }}',
                    text: '{{ __("error_fetching_stats") }}'
                });
            }
        });
    }

    function clearCache() {
        $.ajax({
            url: "{{ route('dashboard.monitoring.clear-cache') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __("success") }}',
                    text: response.message
                });
                fetchStats();
            },
            error: function(xhr, status, error) {
                console.error('Error clearing cache:', error);
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("error_occurred") }}',
                    text: '{{ __("error_clearing_cache") }}'
                });
            }
        });
    }

    // تنظيف الكاش
    document.getElementById('clear-cache-btn').addEventListener('click', function() {
        if (!confirm('هل أنت متأكد من رغبتك في مسح ذاكرة التخزين المؤقت؟')) return;
        clearCache();
    });

    // رسائل النجاح والخطأ
    function showSuccessMessage(message) {
        Swal.fire({
            title: 'نجاح',
            text: message,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    }

    function showErrorMessage(message) {
        Swal.fire({
            title: 'خطأ',
            text: message,
            icon: 'error'
        });
    }

    // تحديث البيانات كل 5 دقائق
    fetchStats();
    setInterval(fetchStats, 5 * 60 * 1000);
});
</script>
@endsection
