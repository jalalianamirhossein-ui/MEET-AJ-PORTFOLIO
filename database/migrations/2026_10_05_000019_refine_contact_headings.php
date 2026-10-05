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
        $content['title_fa'] = 'راه‌های ارتباطی';
        $content['title_en'] = 'Get in Touch';
        $content['heading_fa'] = 'از نیازتان بگویید، با هم راه‌حل می‌سازیم';
        $content['heading_en'] = 'Share Your Needs. Let’s Find a Solution.';
        DB::table('homepage_contents')->where('id', $row->id)->update([
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Preserve editorial changes when rolling back the schema.
    }
};
