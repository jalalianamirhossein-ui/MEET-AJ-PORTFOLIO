<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CompareLegacyContent extends Command
{
    protected $signature = 'site:compare-content';

    protected $description = 'Compare Laravel pages against original HTML sources';

    public function handle(LegacyArticleImporter $importer): int
    {
        if (\App\Models\Service::query()->count() === 0) {
            app(LegacyServiceImporter::class)->import(false);
        }
        if (Article::query()->count() === 0) {
            $importer->import(false);
        }

        $rows = [];
        $fails = 0;

        $home = $this->comparePage('home', base_path('index.html'), $this->render('/'), [
            'id="hero"', 'id="about"', 'id="stats"', 'id="skills"', 'id="resume"',
            'id="services"', 'id="portfolio"', 'id="testimonials"', 'id="contact"',
            'data-fa=', 'php-email-form', 'csrf_token', 'id="service-catalog"',
        ]);
        $rows[] = $home;
        $fails += $home[1] === 'FAIL' ? 1 : 0;

        foreach ([
            'devops-automation', 'monitoring-security', 'network-design',
            'system-administration', 'technical-consulting', 'virtualization-solutions',
        ] as $service) {
            $legacyRequest = \Illuminate\Http\Request::create('/services/'.$service.'.html', 'GET');
            $legacy = $this->laravel->make(\Illuminate\Contracts\Http\Kernel::class)->handle($legacyRequest);
            $this->laravel->make(\Illuminate\Contracts\Http\Kernel::class)->terminate($legacyRequest, $legacy);
            if ($legacy->getStatusCode() !== 301) {
                $rows[] = ['service-redirect:'.$service, 'FAIL', 'expected 301 from .html, got '.$legacy->getStatusCode()];
                $fails++;
            }
            $row = $this->comparePage(
                'service:'.$service,
                base_path('services/'.$service.'.html'),
                $this->render('/services/'.$service),
                ['<h1', 'data-fa=', 'php-email-form', 'csrf_token', 'application/ld+json', 'rel="canonical"']
            );
            $rows[] = $row;
            $fails += $row[1] === 'FAIL' ? 1 : 0;
        }

        $index = $this->comparePage('articles-index', base_path('index.html'), $this->render('/articles'), [
            'id="portfolio"', 'data-fa=',
        ]);
        $rows[] = $index;
        $fails += $index[1] === 'FAIL' ? 1 : 0;

        foreach (Article::query()->orderBy('slug')->get() as $article) {
            $source = File::get(base_path('articles/'.$article->slug.'.html'));
            $sourceBody = $importer->articleBody($source);
            $missing = [];
            foreach (['<h2', 'data-fa=', 'data-en=', 'article-section'] as $token) {
                $original = substr_count($sourceBody, $token);
                $imported = substr_count((string) $article->content, $token);
                if ($original > 0 && $imported < $original) {
                    $missing[] = $token.' source='.$original.' imported='.$imported;
                }
            }
            if (! str_contains((string) $article->content, 'id="')) {
                $missing[] = 'section ids';
            }
            $html = $this->render('/articles/'.$article->slug);
            foreach (['rel="canonical"', 'og:title', 'twitter:card', 'application/ld+json'] as $token) {
                if (! str_contains($html, $token)) {
                    $missing[] = 'seo:'.$token;
                }
            }
            if (str_contains($html, '/articles/'.$article->slug.'.html') && ! str_contains($html, 'redirect')) {
                $missing[] = 'legacy html link in output';
            }
            $rows[] = [$article->slug, $missing ? 'FAIL' : 'PASS', $missing ? implode('; ', $missing) : 'complete body, bilingual attributes, headings, seo'];
            $fails += $missing ? 1 : 0;
        }

        $this->table(['Page', 'Result', 'Notes'], $rows);
        $this->info('Failures: '.$fails);

        return $fails ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  list<string>  $tokens
     * @return array{0:string,1:string,2:string}
     */
    private function comparePage(string $label, string $sourcePath, string $html, array $tokens): array
    {
        $source = File::get($sourcePath);
        $missing = [];
        foreach ($tokens as $token) {
            if (str_contains($source, $token) && ! str_contains($html, $token) && $token !== 'php-email-form') {
                $missing[] = $token;
            }
        }
        if (str_contains($source, 'data-fa=') && substr_count($html, 'data-fa=') < 5) {
            $missing[] = 'sparse data-fa';
        }

        return [$label, $missing ? 'FAIL' : 'PASS', $missing ? implode('; ', $missing) : 'source tokens present'];
    }

    private function render(string $path): string
    {
        $kernel = $this->laravel->make(\Illuminate\Contracts\Http\Kernel::class);
        $request = \Illuminate\Http\Request::create($path, 'GET');
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);
        if ($response->isRedirection()) {
            $location = (string) $response->headers->get('Location');
            $target = parse_url($location, PHP_URL_PATH) ?: $location;
            $query = parse_url($location, PHP_URL_QUERY);
            if ($query) {
                $target .= '?'.$query;
            }

            return $this->render($target);
        }

        return (string) $response->getContent();
    }
}
