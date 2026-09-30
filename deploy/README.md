# اسکریپت‌های Deploy پروژه Meet AJ

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

`setup_env.sh` فقط برای نصب اولیه روی Ubuntu VPS با کاربر سرویس `www-data` است. اگر `.env` موجود باشد متوقف می‌شود تا APP_KEY و تنظیمات سایت حفظ شوند. مقادیر ورودی، از جمله رمز دارای فاصله یا علامت `$` و `#`، با parser واقعی dotenv بررسی و بدون نمایش secret نوشته می‌شوند. برای DirectAdmin از [راهنمای استقرار](../docs/current/DEPLOYMENT.md) استفاده کنید.

`update_project.sh` فایل‌های برنامه و migrationها را به‌روز می‌کند؛ جایگزینی متن مقاله‌های موجود عمدی و جداست. پس از Backup و بررسی ویرایش CMS، فرمان `php artisan articles:import-legacy --update-existing` و سپس `php artisan site:compare-content` را اجرا کنید. Import معمولی محتوای موجود را تغییر نمی‌دهد.
