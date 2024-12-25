# قائمة التحقق للإنتاج

## 1. إعدادات الملف .env
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# تكوين قواعد البيانات
DB_HOST=your_production_host
DB_DATABASE=your_production_database
DB_USERNAME=your_production_username
DB_PASSWORD=your_secure_password

# إعدادات الكاش
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## 2. أوامر ما قبل النشر
```bash
# تثبيت التبعيات وتحسين الأداء
yarn optimize

# تحسين Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# تحسين Composer
composer install --optimize-autoloader --no-dev
```

## 3. الملفات والمجلدات المطلوب نقلها
- ✅ `app/`
- ✅ `bootstrap/`
- ✅ `config/`
- ✅ `database/`
- ✅ `lang/`
- ✅ `public/`
- ✅ `resources/`
- ✅ `routes/`
- ✅ `storage/`
- ✅ `vendor/` (بعد تشغيل composer install)
- ✅ `artisan`
- ✅ `composer.json`
- ✅ `composer.lock`
- ✅ `package.json`
- ✅ `yarn.lock`

## 4. الملفات التي لا يجب نقلها
- ❌ `node_modules/`
- ❌ `.git/`
- ❌ `.env`
- ❌ `tests/`
- ❌ `.editorconfig`
- ❌ `.gitignore`
- ❌ `phpunit.xml`

## 5. صلاحيات الملفات على السيرفر
```bash
# تعيين صلاحيات المجلدات
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data .

# التأكد من صلاحيات الكتابة
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 6. قاعدة البيانات
```bash
# تشغيل المايجريشن
php artisan migrate --force

# تشغيل السيدر (إذا لزم الأمر)
php artisan db:seed
```

## 7. تكوين الويب سيرفر
### Apache
- تأكد من تفعيل mod_rewrite
- تأكد من تكوين Virtual Host
- تأكد من تكوين SSL

### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/your-project/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 8. الأمان
- [ ] تأكد من تعطيل APP_DEBUG
- [ ] تأكد من تفعيل HTTPS
- [ ] تأكد من تحديث جميع كلمات المرور
- [ ] تأكد من تكوين CORS بشكل صحيح
- [ ] تأكد من تكوين session timeout
- [ ] تأكد من تكوين rate limiting

## 9. التحقق النهائي
- [ ] تأكد من عمل جميع الروابط
- [ ] تأكد من عمل تحميل الصور والملفات
- [ ] تأكد من عمل النماذج
- [ ] تأكد من عمل المصادقة
- [ ] تأكد من عمل الإشعارات
- [ ] تأكد من عمل الكاش
- [ ] تأكد من عمل الـ queues
