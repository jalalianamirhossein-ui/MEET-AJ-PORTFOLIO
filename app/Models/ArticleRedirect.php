<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ArticleRedirect extends Model
{
    protected $fillable = ['old_path', 'article_id'];
    public function article(): BelongsTo { return $this->belongsTo(Article::class); }
}
