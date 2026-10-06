# مقاله‌های فنی فارسی و انگلیسی

> Documentation maintenance: 2026-10-06. This document retains its original evidence date and scope; recorded tests and counts were not rerun as part of updating its navigation. Use [current project status](../current/PROJECT-STATUS.md) for current counts, failures and limitations.

آخرین بازبینی: **2026-10-01**؛ تعداد: **۲۵ مقاله** در `resources/legacy/articles`.

فهرست فایل‌ها، عنوان فارسی و انگلیسی، وضعیت ترجمه، بخش‌های لازم و SEO هر مقاله در [bilingual-report.md](bilingual-report.md) و [bilingual-report.json](bilingual-report.json) ثبت شده است. تحلیل اولیه و اولویت‌ها در [report.md](report.md)، [report.json](report.json) و [inventory.json](inventory.json) موجود است.

نسخهٔ اصلی هر فایل بدون تغییر در `originals.zip` محفوظ است. بخش نسخهٔ تاریخی و ردیف اضافهٔ فارسی/انگلیسی از صفحات عمومی حذف شده‌اند. نام فایل و موضوع اصلی حفظ شده‌اند؛ مقاله‌های مقایسه‌ای جدول مقایسه و معیار انتخاب دارند. سناریوها آموزشی‌اند و ادعای تجربهٔ واقعی در شرکت نام‌برده نیستند.

هر مقاله یک آدرس بدون پارامتر زبان دارد. زبان مطابق انتخاب ذخیره‌شدهٔ صفحهٔ اصلی نمایش داده می‌شود؛ بدون انتخاب قبلی، پاسخ سرور انگلیسی است. دکمهٔ شناور زبان، متن و metadata را بدون تغییر URL یا بارگذاری دوباره عوض می‌کند؛ Code Blockها مشترک‌اند. لینک‌های قدیمی دارای پارامتر زبان به آدرس تمیز هدایت می‌شوند. جزئیات: [MULTILINGUAL.md](../current/MULTILINGUAL.md).

## منبع و بازتولید

متن‌های تألیفی در `scripts/prepare-enterprise-runbooks.py` و `scripts/prepare-english-runbooks.py` قرار دارند. جدول‌های مقایسه در `scripts/article_comparisons.py` و بستهٔ کامل SQL Server در `resources/content/articles/sql-server-automatic-backup-job` نگهداری می‌شوند. ابتدا تغییر متن را در منبع تألیفی اعمال کنید؛ تولید دوباره می‌تواند ویرایش دستی HTML را جایگزین کند.

```bash
python scripts/prepare-enterprise-runbooks.py
python scripts/upgrade-enterprise-articles.py
python scripts/prepare-english-runbooks.py
python scripts/build-bilingual-articles.py
python scripts/verify-enterprise-articles.py
```

هر مرحله باید موفق باشد، سپس مرحلهٔ بعد اجرا شود. `runbooks.json` و `english-runbooks.json` خروجی قابل بررسی متن‌ها هستند؛ HTML و گزارش hash از آن‌ها ساخته می‌شوند.

## دیتابیس و استقرار

در این بازبینی، دیتابیس محلی پس از پشتیبان‌گیری کامل با ۲۵ منبع فعلی هماهنگ شد؛ سرور اصلی از این محیط تغییر نکرد. برای سرور، ابتدا Backup و بررسی ویرایش‌های CMS لازم است، سپس:

```bash
php artisan articles:import-legacy --update-existing --dry-run
php artisan articles:import-legacy --update-existing
php artisan site:publish-assets --views
php artisan optimize:clear
php artisan optimize
php artisan site:compare-content
```

Import معمولی مقالهٔ موجود را جایگزین نمی‌کند. `--refresh` برای این به‌روزرسانی لازم نیست. راهنمای کامل در [DEPLOYMENT.md](../current/DEPLOYMENT.md) است.

## نتیجه و حدود بررسی

بررسی ساختار ۲۵ مقاله بدون خطاست؛ تطابق دیتابیس محلی نیز `Failures: 0` دارد. تست‌های پروژه، دو زبان، Headingها، FAQ، Canonical مشترک، حذف لینک‌های زبان جداگانه و یکسان‌بودن کدها را کنترل می‌کنند. [گزارش 2026-10-01](../qa/FULL-AUDIT-2026-10-01.md) نتیجهٔ تست‌ها و مرورگر را ثبت می‌کند.

در بازبینی متن، فعال‌شدن زودهنگام مقصد DFS اصلاح و توضیح تثبیت نسخهٔ Oxidized با دستور واقعی هماهنگ شد. منابع رسمی داخل هر مقاله آمده‌اند. نسخه‌ها baseline مقاله‌اند و حداقل سخت‌افزار رسمی برای همهٔ محیط‌ها محسوب نمی‌شوند. آزمون syntax یا رندر مقاله، جای اجرای Runbook روی تجهیزات هدف و آزمون Restore در staging را نمی‌گیرد.
