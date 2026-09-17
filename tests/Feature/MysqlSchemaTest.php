<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MysqlSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_mysql_engine_constraints_when_connected(): void
    {
        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped('MySQL/MariaDB integration is provided by phpunit.mysql.xml');
        }

        $this->assertTrue(Schema::hasTable('articles'));
        $this->assertTrue(Schema::hasTable('requests'));
        $this->assertTrue(Schema::hasTable('services'));
        $this->assertTrue(Schema::hasTable('tags'));
        $this->assertTrue(Schema::hasTable('article_tag'));
        $charset = DB::selectOne('select @@character_set_database as c, @@collation_database as col');
        $this->assertStringContainsString('utf8mb4', (string) $charset->c);

        $indexes = collect(DB::select('show index from articles'))->pluck('Key_name')->unique();
        $this->assertTrue($indexes->contains('articles_language_slug_unique'));
        $this->assertTrue($indexes->contains('articles_language_status_published_at_index') || $indexes->contains('articles_status_published_at_index'));

        $id = DB::table('articles')->insertGetId([
            'translation_key' => (string) \Illuminate\Support\Str::uuid(),
            'title' => 'متن فارسی MySQL',
            'slug' => 'persian-mysql-check',
            'language' => 'fa',
            'content' => '<p>سلام دنیا</p>',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->assertSame('متن فارسی MySQL', DB::table('articles')->where('id', $id)->value('title'));

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('articles')->insert([
            'translation_key' => (string) \Illuminate\Support\Str::uuid(),
            'title' => 'dup',
            'slug' => 'persian-mysql-check',
            'language' => 'fa',
            'content' => '<p>x</p>',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
