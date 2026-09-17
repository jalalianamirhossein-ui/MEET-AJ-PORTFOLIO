<?php

namespace App\Services;

use App\Models\Article;

class ArticleShareLinks
{
    /**
     * @return array{linkedin:string,whatsapp:string,telegram:string,url:string}
     */
    public function for(Article $article): array
    {
        $url = $article->canonicalUrl();
        $encodedUrl = rawurlencode($url);
        $encodedTitle = rawurlencode($article->title);

        return [
            'url' => $url,
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url='.$encodedUrl,
            'whatsapp' => 'https://wa.me/?text='.$encodedTitle.'%20'.$encodedUrl,
            'telegram' => 'https://t.me/share/url?url='.$encodedUrl.'&text='.$encodedTitle,
        ];
    }
}
