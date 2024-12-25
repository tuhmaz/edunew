<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار SMTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">اختبار إعدادات SMTP</div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="mb-4">
                            <h5>الإعدادات الحالية:</h5>
                            <ul class="list-unstyled">
                                <li>الخادم: {{ config('mail.mailers.smtp.host') }}</li>
                                <li>المنفذ: {{ config('mail.mailers.smtp.port') }}</li>
                                <li>التشفير: {{ config('mail.mailers.smtp.encryption') }}</li>
                                <li>البريد الإلكتروني: {{ config('mail.from.address') }}</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <form action="{{ route('smtp.test') }}" method="GET" class="mb-2">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">اختبار الاتصال</button>
                            </form>

                            <form action="{{ route('smtp.send-test') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">إرسال بريد تجريبي</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
