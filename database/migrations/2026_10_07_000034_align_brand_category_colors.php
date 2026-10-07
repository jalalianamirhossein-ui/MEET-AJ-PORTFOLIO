<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Store this editorial palette in the CMS; later admin edits remain authoritative.
        $colors = [
            'microsoft' => '#0078D4',
            'cisco' => '#049FD9',
            'vmware' => '#607078',
            'mikrotik' => '#293239',
            'fortinet' => '#EE3124',
            'linux' => '#FCC624',
            'supermicro' => '#2B579A',
            'hpe' => '#01A982',
            'ubiquiti' => '#0559C9',
            'juniper' => '#0096A6',
            'avaya' => '#DA291C',
            'qnap' => '#6F2DA8',
            'dell' => '#007DB8',
            'other' => '#A16207',
            'others' => '#A16207',
        ];

        Category::whereIn('slug', array_keys($colors))->get()
            ->each(fn (Category $category) => $category->update(['accent_color' => $colors[$category->slug]]));
    }

    public function down(): void
    {
        // Preserve CMS palette choices rather than replacing later editorial edits.
    }
};
