<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncBrandCategories extends Command
{
    protected $signature = 'categories:sync-brands';
    protected $description = 'Create brand categories and assign articles to their primary brand';

    public function handle(): int
    {
        $brands = [
            'microsoft' => ['Microsoft', '#2563eb'],
            'cisco' => ['Cisco', '#049fd9'],
            'vmware' => ['VMware', '#6d28d9'],
            'mikrotik' => ['MikroTik', '#c2410c'],
            'fortinet' => ['Fortinet', '#ee3124'],
            'linux' => ['Linux', '#15803d'],
            'supermicro' => ['Supermicro', '#2b579a'],
            'hpe' => ['HPE', '#01a982'],
            'ubiquiti' => ['Ubiquiti', '#0559c9'],
            'juniper' => ['Juniper', '#0096a6'],
            'avaya' => ['AVAYA', '#da291c'],
            'qnap' => ['QNAP', '#6f2da8'],
            'dell' => ['DELL', '#007db8'],
            'others' => ['Others', '#a16207'],
        ];
        $categories = [];
        foreach ($brands as $slug => [$name, $color]) {
            $category = Category::query()->firstOrNew(['slug' => $slug, 'language' => 'en']);
            $category->fill(['name' => $name, 'accent_color' => $color, 'sort_order' => count($categories) * 10]);
            $category->translation_key ??= (string) Str::uuid();
            $category->save();
            $categories[$slug] = $category;
        }

        $primary = [
            'netbox' => 'linux', 'oxidized' => 'linux', 'nginx' => 'linux', 'linux' => 'linux',
            'mikrotik' => 'mikrotik', 'sql-server' => 'microsoft', 'vsphere' => 'vmware',
            'vmware' => 'vmware', 'esxi' => 'vmware', 'openvpn' => 'mikrotik', 'ssh' => 'linux',
            'ubuntu' => 'linux', 'windows' => 'microsoft', 'other' => 'others',
        ];
        $updated = 0;
        Article::query()->with('tags')->each(function (Article $article) use ($primary, $categories, &$updated): void {
            $brand = $article->tags->map(fn (Tag $tag) => $tag->slug)->first(fn (string $slug): bool => isset($primary[$slug]));
            $slug = $brand ? $primary[$brand] : 'others';
            $article->updateQuietly(['category_id' => $categories[$slug]->id]);
            $updated++;
        });

        $this->info('Synced '.count($categories).' brand categories and '.$updated.' article assignments.');
        return self::SUCCESS;
    }
}
