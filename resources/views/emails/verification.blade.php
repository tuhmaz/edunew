<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تأكيد البريد الإلكتروني</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ asset('storage/' . config('settings.site_logo')) }}" alt="Logo" style="max-width: 150px;">
        </div>
        
        <h1 style="color: #2c3e50; text-align: center; margin-bottom: 20px;">مرحباً {{ $user->name }}!</h1>
        
        <p style="margin-bottom: 20px; text-align: right;">
            شكراً لتسجيلك في {{ config('app.name') }}. نرجو تأكيد بريدك الإلكتروني بالضغط على الزر أدناه:
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $verificationUrl }}" 
               style="background: #3498db; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                تأكيد البريد الإلكتروني
            </a>
        </div>
        
        <p style="margin-bottom: 20px; text-align: right;">
            إذا لم تقم بإنشاء حساب، يمكنك تجاهل هذا البريد الإلكتروني.
        </p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #666;">
            <p>{{ config('app.name') }} &copy; {{ date('Y') }}</p>
            <p style="font-size: 12px;">
                إذا واجهت مشكلة في الضغط على الزر، يمكنك نسخ ولصق الرابط التالي في متصفحك:<br>
                {{ $verificationUrl }}
            </p>
        </div>
    </div>
</body>
</html>
