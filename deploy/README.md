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
