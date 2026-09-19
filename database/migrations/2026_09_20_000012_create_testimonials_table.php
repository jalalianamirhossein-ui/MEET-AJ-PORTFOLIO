<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->text('quote_en');
            $table->text('quote_fa')->nullable();
            $table->string('author_name');
            $table->string('role_en')->nullable();
            $table->string('role_fa')->nullable();
            $table->string('company_en')->nullable();
            $table->string('company_fa')->nullable();
            $table->string('avatar')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->index(['is_published', 'sort_order']);
        });

        $now = now();
        DB::table('testimonials')->insert([
            [
                'quote_en' => "Amir's expertise in network infrastructure and system administration has been invaluable to our organization. His attention to detail and problem-solving skills are exceptional.",
                'quote_fa' => 'تخصص امیر در زیرساخت شبکه و مدیریت سیستم برای سازمان ما بسیار ارزشمند بوده است. توجه دقیق او به جزئیات و مهارت‌های حل مسئله فوق‌العاده است.',
                'author_name' => 'Newsha Drinks Co.',
                'role_en' => 'IT Director',
                'role_fa' => 'مدیر فناوری اطلاعات',
                'company_en' => 'Newsha Drinks Co.',
                'company_fa' => 'شرکت نوشیدنی نوشا',
                'avatar' => '/assets/img/testimonials/testimonials-1.jpg',
                'sort_order' => 10,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'quote_en' => 'Working with Amir on our virtualization project was a great experience. His technical knowledge and ability to explain complex concepts clearly made the implementation smooth and successful.',
                'quote_fa' => 'همکاری با امیر در پروژه مجازی‌سازی تجربه فوق‌العاده‌ای بود. دانش فنی عمیق او و توانایی در توضیح مفاهیم پیچیده، پیاده‌سازی را روان و موفق کرد.',
                'author_name' => 'Technology Solutions',
                'role_en' => 'Project Manager',
                'role_fa' => 'مدیر پروژه',
                'company_en' => 'Technology Solutions',
                'company_fa' => 'راهکارهای فناوری',
                'avatar' => '/assets/img/testimonials/testimonials-2.jpg',
                'sort_order' => 20,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'quote_en' => "Amir's monitoring and security implementations have significantly improved our system reliability. His proactive approach to IT infrastructure management is commendable.",
                'quote_fa' => 'پیاده‌سازی سیستم‌های نظارت و امنیت امیر، قابلیت اطمینان سیستم ما را به طور چشمگیری بهبود داده است. رویکرد پیشگیرانه او در مدیریت زیرساخت فناوری اطلاعات بسیار قابل تحسین است.',
                'author_name' => 'Enterprise Client',
                'role_en' => 'System Administrator',
                'role_fa' => 'مدیر سیستم',
                'company_en' => 'Enterprise Client',
                'company_fa' => 'مشتری سازمانی',
                'avatar' => '/assets/img/testimonials/testimonials-3.jpg',
                'sort_order' => 30,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'quote_en' => 'The network optimization and load balancing solutions implemented by Amir have greatly enhanced our internet connectivity and overall system performance.',
                'quote_fa' => 'راهکارهای بهینه‌سازی شبکه و تعادل بار پیاده‌سازی شده توسط امیر، اتصال اینترنت و عملکرد کلی سیستم ما را به طور چشمگیری بهبود داده است.',
                'author_name' => 'Infrastructure Team',
                'role_en' => 'Network Engineer',
                'role_fa' => 'مهندس شبکه',
                'company_en' => 'Infrastructure Team',
                'company_fa' => 'تیم زیرساخت',
                'avatar' => '/assets/img/testimonials/testimonials-4.jpg',
                'sort_order' => 40,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'quote_en' => "Amir's expertise in DevOps and automation has streamlined our deployment processes. His technical documentation and knowledge transfer are excellent.",
                'quote_fa' => 'تخصص امیر در DevOps و اتوماسیون، فرآیندهای استقرار ما را به طور قابل توجهی روان کرده است. مستندات فنی و انتقال دانش او بسیار عالی است.',
                'author_name' => 'Development Team',
                'role_en' => 'DevOps Lead',
                'role_fa' => 'سرپرست DevOps',
                'company_en' => 'Development Team',
                'company_fa' => 'تیم توسعه',
                'avatar' => '/assets/img/testimonials/testimonials-5.jpg',
                'sort_order' => 50,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
