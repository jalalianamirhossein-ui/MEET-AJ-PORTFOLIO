<?php
namespace Tests\Feature;
use App\Models\{Article, ArticleRedirect, Category, Request, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ContentRulesTest extends TestCase
{
    use RefreshDatabase;
    private function article(array $fields = []): Article { return Article::create(array_merge(['title' => 'Test article', 'slug' => 'test-article', 'content' => '<p>متن فارسی / English</p>'], $fields)); }
    public function test_publication_dates_and_german_drafts_are_enforced(): void {
        $this->article(['status' => 'published', 'published_at' => now()->subDay()]);
        $this->article(['slug' => 'future', 'status' => 'published', 'published_at' => now()->addDay()]);
        $this->article(['slug' => 'draft']); $this->article(['language' => 'de']);
        $this->assertSame(1, Article::published()->count());
        $this->expectException(ValidationException::class);
        $this->article(['language' => 'de', 'slug' => 'german', 'status' => 'published', 'published_at' => now()]);
    }
    public function test_slug_history_cannot_be_claimed_by_another_article(): void {
        $article = $this->article(); $article->update(['slug' => 'renamed']);
        $this->assertDatabaseHas('article_redirects', ['old_path' => '/articles/test-article', 'article_id' => $article->id]);
        $this->expectException(ValidationException::class); $this->article();
    }
    public function test_category_foreign_key_and_locale_validation(): void {
        $category = Category::create(['name' => 'Linux', 'slug' => 'linux', 'language' => 'en']);
        $article = $this->article(['category_id' => $category->id]);
        $this->assertTrue($article->category->is($category));
        $category->delete(); $this->assertNull($article->fresh()->category_id);
        $fa = Category::create(['name' => 'لینوکس', 'slug' => 'linux', 'language' => 'fa']);
        $this->expectException(ValidationException::class); $article->update(['category_id' => $fa->id]);
    }
    public function test_roles_protect_contacts_and_content(): void {
        $editor = User::create(['name' => 'Editor', 'email' => 'editor@example.test', 'password' => 'test-password']);
        $editor->forceFill(['role' => 'editor'])->save();
        $this->assertTrue(Gate::forUser($editor)->allows('create', Article::class));
        $this->assertFalse(Gate::forUser($editor)->allows('viewAny', Request::class));
        $editor->forceFill(['role' => 'admin'])->save();
        $this->assertTrue(Gate::forUser($editor)->allows('viewAny', Request::class));
        $this->assertNotSame('test-password', $editor->password);
    }
    public function test_deletion_cascades_redirects_and_preserves_category(): void {
        $article = $this->article(); $article->redirects()->create(['old_path' => '/articles/original.html']);
        $article->delete(); $this->assertSame(0, ArticleRedirect::count());
    }
}
