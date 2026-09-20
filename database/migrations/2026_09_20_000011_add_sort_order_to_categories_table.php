<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('slug');
            $table->index(['language', 'sort_order']);
        });

        $defaults = [
            'microsoft' => 10,
            'linux' => 20,
            'mikrotik' => 30,
            'vmware' => 40,
            'others' => 50,
        ];

        foreach ($defaults as $slug => $order) {
            DB::table('categories')->where('slug', $slug)->update(['sort_order' => $order]);
        }

        $next = 60;
        DB::table('categories')
            ->whereNotIn('slug', array_keys($defaults))
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $id) use (&$next): void {
                DB::table('categories')->where('id', $id)->update(['sort_order' => $next]);
                $next += 10;
            });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_language_sort_order_index');
            $table->dropColumn('sort_order');
        });
    }
};
