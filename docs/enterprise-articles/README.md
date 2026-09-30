# گزارش ارتقای مقالات زیرساخت

نسخه نهایی اکنون دوزبانه است: [گزارش مقاله‌به‌مقاله](bilingual-report.md)، [SEO و وضعیت قابل پردازش](bilingual-report.json)، [ارزیابی قبل تغییر زبان](bilingual-before.json). هر دو زبان متن اجرایی کامل دارند؛ نسخه تاریخی به زبان اصلی نگهداری می‌شود. headingها و code blockها در ۵۰ پاسخ سرور با یکدیگر تطبیق داده شده‌اند.

انگلیسی و SEO پیش‌فرض: `/articles/{slug}`؛ فارسی: `/articles/{slug}?lang=fa`. لینک قدیمی `?lang=en` همچنان انگلیسی است و Canonical آن URL اصلی است. هر نسخه Hreflang متقابل و FAQ Schema همان زبان دارد. دکمه زبان و لینک ترجمه، متن و metadata موجود را بدون Reload عوض می‌کنند؛ با JavaScript غیرفعال، هر دو نسخه از سرور کامل برمی‌گردند. [مستند رسمی Google](https://developers.google.com/search/docs/specialty/international/localized-versions) مبنای رابطه نسخه‌هاست.

اصلاح موضوع مقاله‌ها: vSS/vDS دوباره مقایسه قابلیت‌ها و معیار انتخاب است؛ مهاجرت بخش عملی بعد از تصمیم معماری است. جدول مقایسه برای HTTP/HTTPS، IMAP/POP3 و CMD/PowerShell/DxDiag بازگردانده شد. مقاله Dual-WAN هدف اصلی توزیع نامتقارن را حفظ می‌کند و تفاوت ECMP با مثال PCC را صریح توضیح می‌دهد. عنوان تکراری Enterprise و سازمان فرضی یکسان از مثال‌های جدید حذف شدند؛ اشاره به Edition واقعی SQL Server حفظ شده است. متن این اصلاحات در `scripts/article_comparisons.py` نگهداری می‌شود.

آزمون‌های مرتبط اصلاحات 2026-10-01: ۲۲ تست، ۴۳۶۶ assertion موفق. اعتبارسنجی HTML/FAQ/TOC/hash هر ۲۵ فایل بدون خطا و بررسی syntax JavaScript موفق است. تغییر زبان در مقاله vSS/vDS و مقاله بلند SQL Server بدون درخواست جدید برای document بررسی شد؛ متن، عنوان، RTL/LTR، Canonical، FAQ Schema، کدها، لینک کپی و Related هماهنگ‌اند. تنها بارگذاری موردنیاز در اولین تغییر به فارسی CSS زبان است. تصاویر corrected-vsphere-fa.jpg و corrected-vsphere-en.jpg ثبت شده‌اند. اجرای عملی زیرساخت جزو این آزمون‌ها نیست.

نتیجه کل مجموعه تست پروژه: ۸۱ تست، ۵۲۷۹ assertion، یک failure و یک skip. تنها failure در ProductionAuditTest::test_persian_testimonials_use_shared_rtl_safe_slider است: تست انتظار main.js?v=1414 دارد اما صفحه اصلی main.js?v=1415 را استفاده می‌کند. این اختلاف در نسخه HEAD پیش از تغییرات این کار نیز وجود دارد؛ صفحه اصلی و این بررسی نامرتبط تغییر نکرده‌اند. کنترل عنوان مقاله در ProductionAuditTest با رفتار FA/EN جدید هماهنگ شده و موفق است.

بازسازی از مبنای اصلی: ابتدا `prepare-enterprise-runbooks.py` و `upgrade-enterprise-articles.py`، سپس `prepare-english-runbooks.py` و `build-bilingual-articles.py` اجرا شوند. مرحله آخر متن تألیفی انگلیسی را با کد مشترک ترکیب می‌کند؛ اجرای مکرر آن تغییری ایجاد نمی‌کند. این اسکریپت‌ها HTML را می‌نویسند؛ قبل اجرا ویرایش دستی را با source تألیفی هماهنگ کنید.

فایل‌های منبع جایگزین و پشتیبانی زبان در برنامه تکمیل شده است؛ دیتابیس عملیاتی و سایت منتشرشده تغییر نکرده‌اند. برای استقرار، ابتدا `php artisan articles:import-legacy --update-existing --dry-run` و پس از بازبینی، `php artisan articles:import-legacy --update-existing` در workflow استقرار اجرا شوند. انتشار asset جدید i18n.js نیز لازم است. این فرمان‌ها در این کار روی دیتابیس عملیاتی اجرا نشده‌اند.

تاریخ بررسی: 2026-09-30. تعداد: ۲۵ فایل HTML در `resources/legacy/articles`.

فهرست کامل، ساختار قبلی، دستورات و منابع استخراج‌شده در `inventory.json` ثبت شده است. اولویت ۱: SSH، Netplan، مدیریت حساب، OpenVPN، Dual-WAN، فیلتر وب و اسکن، Downgrade، SQL Backup و بازیابی Windows. اولویت ۲: NetBox، Nginx، Oxidized، DFS، ESXi، vSwitch، TLS و زمان. سایر موضوعات اولویت ۳ هستند.

روش حفظ محتوا: فایل اصلی پیش از تغییر به‌صورت کامل در `originals.zip` نگهداری می‌شود. متن مقاله قدیمی در بخش تاریخی همان مقاله باقی می‌ماند؛ نمونه‌های تاریخی دستورالعمل اجرایی نسخه جدید نیستند. URL و نام فایل حفظ می‌شوند و URL بدون پسوند با مسیر Laravel هم‌راستا است.

تحقیق: مستندات رسمی Canonical/OpenSSH/Netplan، MikroTik، Microsoft Learn، NetBox، Nginx، Oxidized و Broadcom. سناریوهای سازمانی آموزشی و فرضی هستند. اندازه سخت‌افزار پیشنهادی مقاله، حداقل رسمی سازنده محسوب نمی‌شود. نسخه‌ها baseline هستند؛ انتخاب patch در Change Record ثبت می‌شود.

محدودیت اعتبارسنجی: این workspace تجهیزات RouterOS، ESXi، Windows Server، SQL Server و VM لینوکس هدف را در اختیار ندارد. اعتبارسنجی ساختار HTML، JSON-LD، حفظ متن و سازگاری importer انجام می‌شود؛ اجرای عملی دستورات روی این پلتفرم‌ها باید در staging انجام شود. موفقیت تست ساختاری به معنی تأیید عملکرد در Production نیست.

نتیجه بررسی نهایی: هر ۲۵ فایل جایگزین شد. بررسی بخش‌ها، metadata فارسی، FAQ/متن، لینک‌های فهرست، hash نسخه جدید و archive اصلی بدون خطا است (validation.json). مجموعه تست‌های ArticleLibrary، ArticleFaqTranslation، SqlBackupArticle و ArticleImage: ۱۶ تست، ۳۲۰ assertion، همگی موفق. syntax چهل block Bash و یازده block PowerShell بررسی شد؛ Python تولید تنظیمات NetBox نیز parse می‌شود. این بررسی‌ها دستورات زیرساخت را اجرا نمی‌کنند.

گزارش قابل مطالعه نام فایل‌ها، تحلیل و SEO: report.md. داده قابل پردازش: report.json. متن تألیفی runbookها: runbooks.json. اسکریپت scripts/upgrade-enterprise-articles.py از archive اولیه بازتولید می‌کند؛ پس از ویرایش دستی HTML آن را بدون بررسی دوباره اجرا نکنید، چون مبنا نسخه archive است. این کار فایل‌های منبع را تغییر داده است؛ دیتابیس عملیاتی یا سایت منتشرشده در این مرحله تغییر داده نشده‌اند.

مقاله SQL Server بسته مستقل و اسکریپت‌های کامل موجود دارد؛ آن بسته حفظ می‌شود. هش baseline تاریخی در `docs/qa/baseline-files.json` تغییر نمی‌کند؛ ابزار verify-originals پس از بازنویسی، تغییر مجاز این ۲۵ فایل را گزارش خواهد کرد.
