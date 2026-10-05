<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $row = DB::table('homepage_contents')->where('key', 'contact')->first();
        if (! $row) {
            return;
        }
        $content = json_decode($row->content, true, 512, JSON_THROW_ON_ERROR);
        $content['intro_fa'] = 'برای مشاوره یا اجرای پروژه‌های IT، در ارتباط باشیم.';
        $content['intro_en'] = 'Let’s connect for IT consulting or your next project.';
        DB::table('homepage_contents')->where('id', $row->id)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Preserve editorial changes on schema rollback.
    }
};
