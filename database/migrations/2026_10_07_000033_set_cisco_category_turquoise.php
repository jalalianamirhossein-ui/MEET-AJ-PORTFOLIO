<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Category::where('slug', 'cisco')->whereIn('language', ['en', 'fa'])
            ->get()->each(fn (Category $category) => $category->update(['accent_color' => '#049FD9']));
    }

    public function down(): void
    {
        // Preserve the requested editorial color on rollback.
    }
};
