<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use GuzzleHttp\TransferStats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

class CheckSiteAssets extends Command
{
    protected $signature = 'site:check-assets {--url= : Public origin; defaults to APP_URL} {--path=* : Pages to check; defaults to /admin/login and /}';

    protected $description = 'Check public page CSS, JavaScript and preloaded fonts for mixed content, HTTP and MIME failures (read-only)';

    public function handle(): int
    {
        $origin = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        if (! filter_var($origin, FILTER_VALIDATE_URL) || ! in_array(parse_url($origin, PHP_URL_SCHEME), ['http', 'https'], true)) {
            $this->error('Use an absolute HTTP or HTTPS public origin.');

            return self::FAILURE;
        }

        $failed = false;
        $checked = [];
        foreach ($this->option('path') ?: ['/admin/login', '/'] as $path) {
            $pageUrl = $origin.'/'.ltrim($path, '/');
            try {
                [$page, $effectiveUrl] = $this->fetch($pageUrl);
                if ($page->status() !== 200 || ! str_contains(strtolower($page->header('Content-Type')), 'text/html')) {
                    throw new \RuntimeException('Expected HTTP 200 HTML.');
                }
                if ($this->isDowngrade($pageUrl, $effectiveUrl)) {
                    throw new \RuntimeException('HTTPS page redirects to HTTP.');
                }

                $dom = new DOMDocument;
                $previous = libxml_use_internal_errors(true);
                try {
                    $dom->loadHTML($page->body(), LIBXML_NONET);
                } finally {
                    libxml_clear_errors();
                    libxml_use_internal_errors($previous);
                }
                $xpath = new DOMXPath($dom);
                $base = $xpath->evaluate('string(//base[@href][1]/@href)');
                $baseUrl = $base ? (string) UriResolver::resolve(new Uri($effectiveUrl), new Uri($base)) : $effectiveUrl;
                $nodes = $xpath->query('//link[@rel="stylesheet" or @rel="modulepreload" or @rel="preload"] | //script[@src]');
                $stylesheets = 0;
                foreach ($nodes as $node) {
                    $rel = $node->getAttribute('rel');
                    $kind = $node->nodeName === 'script' || $rel === 'modulepreload' ? 'script' : ($rel === 'stylesheet' ? 'style' : $node->getAttribute('as'));
                    if (! in_array($kind, ['style', 'script', 'font'], true)) {
                        continue;
                    }
                    $stylesheets += $kind === 'style' ? 1 : 0;
                    $reference = $node->getAttribute($node->nodeName === 'script' ? 'src' : 'href');
                    $assetUrl = (string) UriResolver::resolve(new Uri($baseUrl), new Uri($reference));
                    $key = $kind.':'.$assetUrl;
                    if (isset($checked[$key])) {
                        continue;
                    }
                    $checked[$key] = true;
                    try {
                        if (! in_array(parse_url($assetUrl, PHP_URL_SCHEME), ['http', 'https'], true) || $this->isDowngrade($effectiveUrl, $assetUrl)) {
                            throw new \RuntimeException('Insecure or unsupported asset URL.');
                        }
                        [$asset, $effectiveAssetUrl] = $this->fetch($assetUrl);
                        $mime = strtolower(trim(explode(';', $asset->header('Content-Type'))[0]));
                        $validMime = match ($kind) {
                            'style' => $mime === 'text/css',
                            'script' => in_array($mime, ['application/javascript', 'text/javascript', 'application/x-javascript'], true),
                            'font' => str_starts_with($mime, 'font/') || in_array($mime, ['application/font-woff', 'application/vnd.ms-fontobject', 'application/octet-stream'], true),
                        };
                        if ($asset->status() !== 200 || ! $validMime || trim($asset->body()) === '' || $this->isDowngrade($effectiveUrl, $effectiveAssetUrl)) {
                            throw new \RuntimeException('Expected nonempty HTTP 200 '.$kind.' with the correct MIME type; got '.$asset->status().' '.$mime.'.');
                        }
                        $this->line('OK '.$kind.' '.$assetUrl);
                    } catch (Throwable $exception) {
                        $failed = true;
                        $this->error('FAIL '.$assetUrl.' — '.$exception->getMessage());
                    }
                }
                if ($stylesheets === 0) {
                    throw new \RuntimeException('No stylesheet links found; check the rendered layout.');
                }
                $this->line('Checked page '.$effectiveUrl);
            } catch (Throwable $exception) {
                $failed = true;
                $this->error('FAIL '.$pageUrl.' — '.$exception->getMessage());
            }
        }

        $this->line($failed ? 'Asset checks failed. Do not mark this deployment healthy.' : 'Asset checks passed. Verify browser interactions separately.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function fetch(string $url): array
    {
        $effectiveUrl = $url;
        $response = Http::connectTimeout(10)->timeout(30)->withOptions([
            'allow_redirects' => [
                'max' => 5,
                // Never follow even an intermediate downgrade from HTTPS.
                'protocols' => parse_url($url, PHP_URL_SCHEME) === 'https' ? ['https'] : ['http', 'https'],
            ],
            'on_stats' => function (TransferStats $stats) use (&$effectiveUrl): void {
                $effectiveUrl = (string) $stats->getEffectiveUri();
            },
        ])->get($url);

        return [$response, $effectiveUrl];
    }

    private function isDowngrade(string $page, string $asset): bool
    {
        return parse_url($page, PHP_URL_SCHEME) === 'https' && parse_url($asset, PHP_URL_SCHEME) !== 'https';
    }
}
