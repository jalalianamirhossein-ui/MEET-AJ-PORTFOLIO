<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $rows = [
            ['quote_en' => 'Amir designed and launched our large-scale network architecture with a clear understanding of every layer, from physical connectivity and switching to routing, security, and service delivery. His structured approach made a complex environment easier to operate and grow.', 'quote_fa' => 'امیر معماری شبکهٔ بزرگ ما را طراحی و راه‌اندازی کرد و درک دقیقی از تمام لایه‌ها، از اتصال فیزیکی و سوئیچینگ تا Routing، امنیت و ارائهٔ سرویس داشت. رویکرد ساختارمند او محیط پیچیدهٔ ما را برای بهره‌برداری و توسعه ساده‌تر کرد.', 'author_name' => 'Infrastructure Client', 'role_en' => 'Mehdi Sadeghi', 'role_fa' => 'مهدی صادقی', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-0.jpg'],
            ['quote_en' => 'Amir improved our server and network infrastructure and turned scattered operational knowledge into reliable documentation. The new standards, diagrams, and runbooks made maintenance faster and reduced dependency on individual team members.', 'quote_fa' => 'امیر زیرساخت سرورها و شبکهٔ ما را بهبود داد و دانش پراکندهٔ عملیاتی را به مستندات قابل اتکا تبدیل کرد. استانداردها، دیاگرام‌ها و Runbookهای جدید نگهداری را سریع‌تر و وابستگی به افراد را کمتر کرد.', 'author_name' => 'IT Director', 'role_en' => 'Alireza Sabzchian', 'role_fa' => 'علیرضا سبزه چیان', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-1.jpg'],
            ['quote_en' => 'Amir implemented Jira and a practical project-management workflow for our company. We gained clearer ownership, realistic reporting, and a shared view of progress across teams without adding unnecessary process overhead.', 'quote_fa' => 'امیر Jira و یک فرآیند کاربردی مدیریت پروژه را برای شرکت ما راه‌اندازی کرد. مالکیت کارها، گزارش‌دهی واقعی و دید مشترک تیم‌ها نسبت به پیشرفت شفاف شد، بدون اینکه فرآیندهای اضافی ایجاد شود.', 'author_name' => 'Commercial Manager', 'role_en' => 'Mazdak Alipour', 'role_fa' => 'مزدک علیپور', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-20.jpg'],
            ['quote_en' => 'Amir prepared our web server, containerized the application, and put the website behind a properly configured CDN. The deployment became repeatable, faster for users, and much easier for our development team to support.', 'quote_fa' => 'امیر Web Server ما را آماده کرد، پروژه و سایت را Dockerize کرد و آن را پشت CDN با پیکربندی صحیح قرار داد. استقرار تکرارپذیرتر، سرعت دسترسی کاربران بهتر و پشتیبانی برای تیم توسعه ساده‌تر شد.', 'author_name' => 'Senior Java Developer', 'role_en' => 'Mani Nasrollahi', 'role_fa' => 'مانی نصراللهی', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-3.jpg'],
            ['quote_en' => 'The Jira upgrade from version 6 to version 9 was completed with careful database migration, compatibility checks, and a controlled rollback plan. Amir kept the service available and the data protected throughout the transition.', 'quote_fa' => 'ارتقای Jira از نسخهٔ ۶ به ۹ با Migration دقیق دیتابیس، بررسی سازگاری و برنامهٔ Rollback کنترل‌شده انجام شد. امیر در تمام این انتقال، در دسترس‌بودن سرویس و سلامت داده‌ها را حفظ کرد.', 'author_name' => 'IT Director', 'role_en' => 'Ashkan Afsharpad', 'role_fa' => 'اشکان افشارپاد', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-4.jpg'],
            ['quote_en' => 'Amir designed and deployed our QNAP storage environment for dependable data retention and controlled access. The storage structure, permissions, and backup approach gave our content team a safer and more organized workflow.', 'quote_fa' => 'امیر زیرساخت ذخیره‌سازی QNAP ما را برای نگهداری مطمئن داده و دسترسی کنترل‌شده طراحی و راه‌اندازی کرد. ساختار Storage، Permissionها و روش Backup، گردش کار تیم محتوا را امن‌تر و منظم‌تر کرد.', 'author_name' => 'Content Manager', 'role_en' => 'Behtash Ghadrdoost', 'role_fa' => 'بهتاش قدردوست', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-50.jpg'],
            ['quote_en' => 'Amir designed the network architecture for our factory CCTV and alarm system, separating monitoring traffic and access controls in a dependable layout. The result gave management clearer visibility and the security team a system they could trust.', 'quote_fa' => 'امیر معماری شبکهٔ سیستم دوربین مداربسته و دزدگیر کارخانهٔ ما را طراحی کرد و ترافیک نظارت و کنترل دسترسی را در ساختاری مطمئن جدا کرد. نتیجه، دید بهتر برای مدیریت و سیستمی قابل اعتماد برای تیم امنیت بود.', 'author_name' => 'Operations Manager', 'role_en' => 'Amir Ghorbani', 'role_fa' => 'امیر قربانی', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-6.jpg'],
            ['quote_en' => 'Amir delivered a complete architecture for our network, website, and video-surveillance systems. He connected the business requirements to a practical, secure design that can grow with the company.', 'quote_fa' => 'امیر معماری کامل شبکه، سایت و سیستم نظارت تصویری ما را طراحی و اجرا کرد. نیازهای کسب‌وکار به طراحی‌ای کاربردی، امن و قابل توسعه برای آیندهٔ شرکت تبدیل شد.', 'author_name' => 'Chief Executive Officer', 'role_en' => 'Seyed Hossein AghaSeyd Morteza', 'role_fa' => 'سیدحسین آقاسیدمرتضی', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-7.jpg'],
            ['quote_en' => 'Amir was a hardworking and curious student who consistently turned difficult Microsoft infrastructure topics into practical skills. His discipline, questions, and steady progress show a bright future in IT.', 'quote_fa' => 'امیر شاگردی سخت‌کوش و کنجکاو بود و مباحث دشوار زیرساخت Microsoft را به مهارت‌های عملی تبدیل می‌کرد. نظم، پرسشگری و پیشرفت مداوم او آینده‌ای روشن در IT را نشان می‌دهد.', 'author_name' => 'Microsoft Instructor', 'role_en' => 'Mohamad Samimi', 'role_fa' => 'محمد صمیمی', 'company_en' => '', 'company_fa' => '', 'avatar' => '/assets/img/testimonials/testimonials-8.jpg'],
        ];

        DB::transaction(function () use ($rows): void {
            foreach ($rows as $index => $row) {
                DB::table('testimonials')
                    ->where('quote_en', $row['quote_en'])
                    ->orWhereIn('avatar', [$row['avatar'], '/assets/img/testimonials/testimonials-'.($index + 10).'.jpg'])
                    ->update(
                    $row + ['updated_at' => now()]
                );
            }
        });
    }

    public function down(): void
    {
        // Editorial content updates are retained on schema rollback.
    }
};
