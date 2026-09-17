              <nav class="article-breadcrumb" aria-label="Breadcrumb">
                <ol>
                  <li><a href="/" data-en="Home" data-fa="خانه">Home</a></li>
                  <li><a href="/articles" data-en="Articles" data-fa="مقالات">Articles</a></li>
                  @if ($article->category)
                    <li><a href="/articles">{{ $article->category->name }}</a></li>
                  @endif
                  <li aria-current="page">{{ $article->title }}</li>
                </ol>
              </nav>
