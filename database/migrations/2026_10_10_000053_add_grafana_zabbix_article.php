<?php

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(LegacyArticleImporter::class)->import(false, false, [
            'grafana-installation-zabbix-integration',
        ]);

        $zabbix = Article::where('slug', 'zabbix-server-linux-windows-agents-backup')->first();
        $path = '/articles/grafana-installation-zabbix-integration';
        if ($zabbix && ! str_contains((string) $zabbix->content, $path)) {
            $link = '<p><a href="'.$path.'" rel="noopener"><span data-en="Related: Grafana dashboards integrated with Zabbix" data-fa="مرتبط: داشبورد گرافانا متصل به زبیکس">Related: Grafana dashboards integrated with Zabbix</span></a></p>';
            $content = (string) $zabbix->content;
            $footer = stripos($content, '<footer');
            $content = $footer === false
                ? $content."\n".$link
                : substr($content, 0, $footer).$link."\n".substr($content, $footer);
            Article::withoutTimestamps(fn () => $zabbix->forceFill(['content' => $content])->save());
        }
    }

    public function down(): void
    {
        // Preserve editorial content during schema rollback.
    }
};
