<?php

use App\Services\ImagePaths;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rewrite image locations only, without reimporting editorial content
        // or changing publication dates, timestamps, IDs and upload paths.
        DB::transaction(function (): void {
            foreach (DB::table('articles')->orderBy('id')->get() as $article) {
                $changes = [];
                foreach (['featured_image', 'content', 'presentation', 'seo_data'] as $column) {
                    $before = $article->$column;
                    if ($before === null) {
                        continue;
                    }
                    if (in_array($column, ['presentation', 'seo_data'], true)) {
                        $decoded = json_decode($before, true, 512, JSON_THROW_ON_ERROR);
                        $after = json_encode(ImagePaths::data($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                        if (ImagePaths::data($decoded) === $decoded) {
                            continue;
                        }
                    } else {
                        $after = $column === 'content'
                            ? ImagePaths::body($before, $article->slug)
                            : ImagePaths::rewrite($before);
                    }
                    if ($after !== $before) {
                        $changes[$column] = $after;
                    }
                }
                if ($changes) {
                    DB::table('articles')->where('id', $article->id)->update($changes);
                }
            }
            foreach (DB::table('homepage_contents')->orderBy('id')->get() as $section) {
                $before = json_decode($section->content, true, 512, JSON_THROW_ON_ERROR);
                $after = ImagePaths::data($before);
                if ($before !== $after) {
                    DB::table('homepage_contents')->where('id', $section->id)->update([
                        'content' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
                    ]);
                }
            }
            foreach (DB::table('testimonials')->whereNotNull('avatar')->get() as $testimonial) {
                $after = ImagePaths::rewrite($testimonial->avatar);
                if ($after !== $testimonial->avatar) {
                    DB::table('testimonials')->where('id', $testimonial->id)->update(['avatar' => $after]);
                }
            }
        });
    }

    public function down(): void
    {
        // Keep the organized paths: reversing them would point at retired files.
    }
};
