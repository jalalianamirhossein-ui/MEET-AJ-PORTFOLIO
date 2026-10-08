# اسکریپت‌های Deploy پروژه Meet AJ

Security update, 2026-10-08: use [the deployment checklist](../DEPLOYMENT.md) for `/var/www/meetaj` and review [the nginx example](nginx.conf.example) before adapting certificate and PHP-FPM paths. Set `FORCE_HTTPS=true`, an HTTPS `APP_URL`, and actual `TRUSTED_PROXIES`; preserve the existing environment and key. The example has not been run through nginx on this Windows host. Deployment and database changes remain operator actions.

اسکریپت‌ها را از ریشه پروژه اجرا کنید:

```bash
chmod +x deploy/*.sh

sudo bash deploy/install_requirements.sh
bash deploy/clone_project.sh
sudo bash deploy/setup_env.sh
sudo bash deploy/update_project.sh
```

ترتیب پیشنهادی اجرای اولیه:

1. `install_requirements.sh`
2. ساخت دیتابیس و یوزر MySQL
3. `clone_project.sh`
4. `setup_env.sh`
5. تنظیم Nginx و SSL

برای repository خصوصی، آدرس SSH مثل `git@github.com:user/repository.git` بدهید و قبل از clone، Deploy Key سرور را در Git provider ثبت کنید.

اسکریپت `update_project.sh` اگر تغییر محلی پیدا کند متوقف می‌شود و از overwrite کردن آن‌ها جلوگیری می‌کند.

فایل `public/css/app/meet-aj-admin.css` خروجی تولیدشدهٔ `php artisan filament:assets` است و در Git نگهداری نمی‌شود. منبع قابل ویرایش آن `resources/css/filament-admin.css` است. اگر checkout قدیمی هنگام انتشار فقط برای تغییر این خروجی متوقف شد، ابتدا نسخهٔ محلی را خارج از پروژه ذخیره کنید، سپس همان فایل را به نسخهٔ Git برگردانید و انتشار را دوباره اجرا کنید:

```bash
cd /var/www/meetaj
cp public/css/app/meet-aj-admin.css "/var/tmp/meet-aj-admin.css.$(date +%Y%m%d-%H%M%S).bak"
git restore -- public/css/app/meet-aj-admin.css
/opt/deploy/update_project.sh
```

این راهکار برای همان خروجی CSS است؛ تغییرات سایر فایل‌ها را بررسی و حفظ کنید. اسکریپت انتشار فایل CSS را پس از دریافت کد جدید بازتولید می‌کند.

`setup_env.sh` فقط برای نصب اولیه روی Ubuntu VPS با کاربر سرویس `www-data` است. اگر `.env` موجود باشد متوقف می‌شود تا APP_KEY و تنظیمات سایت حفظ شوند. مقادیر ورودی، از جمله رمز دارای فاصله یا علامت `$` و `#`، با parser واقعی dotenv بررسی و بدون نمایش secret نوشته می‌شوند. برای DirectAdmin از [راهنمای استقرار](../docs/current/DEPLOYMENT.md) استفاده کنید.

`update_project.sh` فایل‌های برنامه و migrationها را به‌روز می‌کند؛ جایگزینی متن مقاله‌های موجود عمدی و جداست. پس از Backup و بررسی ویرایش CMS، فرمان `php artisan articles:import-legacy --update-existing` و سپس `php artisan site:compare-content` را اجرا کنید. Import معمولی محتوای موجود را تغییر نمی‌دهد.

بازبینی مستندات: 2026-10-06. منابع مقاله‌ها در `resources/content/articles/` باید در بستهٔ انتشار بمانند. پوشهٔ Linux اکنون `linux-security-auditor-bash` است و آدرس دانلود عمومی تغییر نکرده است. نتیجهٔ بررسی محلی و اختلاف منابع با دیتابیس در [وضعیت فعلی](../docs/current/PROJECT-STATUS.md) ثبت شده؛ اجرای این بررسی به معنی تأیید سرور نیست.
