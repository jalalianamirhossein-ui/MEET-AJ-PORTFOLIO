<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $copy = [
            1 => [
                'quote_en' => 'Amir turned our Linux and network administration work into a clear, documented process. The NetBox and Ubuntu guidance was practical, secure, and easy for our team to maintain.',
                'quote_fa' => 'امیر مدیریت لینوکس و شبکهٔ ما را به فرآیندی شفاف و مستند تبدیل کرد. راهکارهای NetBox و Ubuntu او کاربردی، امن و قابل نگهداری برای تیم ما بود.',
            ],
            2 => [
                'quote_en' => 'The VMware ESXi and vSphere configuration was explained clearly from design to validation. We completed the virtualization work with a reliable rollback plan and far fewer surprises.',
                'quote_fa' => 'پیکربندی VMware ESXi و vSphere از طراحی تا اعتبارسنجی به‌روشنی توضیح داده شد. پروژهٔ مجازی‌سازی را با Rollback Plan مطمئن و ریسک بسیار کمتر به پایان رساندیم.',
            ],
            3 => [
                'quote_en' => 'Amir improved our MikroTik security and VPN configuration with careful testing. The final setup was easier to monitor, safer to operate, and well documented for future changes.',
                'quote_fa' => 'امیر پیکربندی امنیتی و VPN میکروتیک ما را با تست دقیق بهبود داد. تنظیمات نهایی امن‌تر، قابل مانیتور و برای تغییرات آینده به‌خوبی مستند شده است.',
            ],
            4 => [
                'quote_en' => 'Our dual-WAN and network performance issues were diagnosed methodically. The load-balancing design made the connection more stable and gave our team clear checks for failover.',
                'quote_fa' => 'مشکلات Dual-WAN و عملکرد شبکهٔ ما به‌صورت مرحله‌ای عیب‌یابی شد. طراحی Load Balancing اتصال را پایدارتر کرد و بررسی Failover را برای تیم ما شفاف ساخت.',
            ],
            5 => [
                'quote_en' => 'Amir connected infrastructure operations with repeatable DevOps practices. His automation, security notes, and knowledge transfer helped us deploy changes with confidence.',
                'quote_fa' => 'امیر عملیات زیرساخت را به روش‌های تکرارپذیر DevOps متصل کرد. Automation، نکات امنیتی و انتقال دانش او باعث شد تغییرات را با اطمینان بیشتری Deploy کنیم.',
            ],
        ];

        foreach ($copy as $id => $values) {
            DB::table('testimonials')->where('id', $id)->update($values);
        }
    }

    public function down(): void
    {
        // Editorial copy is intentionally not restored automatically.
    }
};
