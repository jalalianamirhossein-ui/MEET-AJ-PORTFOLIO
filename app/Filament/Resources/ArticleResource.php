<?php
namespace App\Filament\Resources;

use App\Models\Article;
use Filament\Actions\{CreateAction, EditAction, DeleteAction};
use Filament\Forms\Components\{TextInput, Textarea, RichEditor, Select, DateTimePicker, FileUpload};
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\{Table, Columns\TextColumn, Filters\SelectFilter};

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;
    public static function form(Schema $schema): Schema {
        $content = $schema->getRecord()?->presentation
            ? Textarea::make('content')->label('Article HTML')->rows(24)->helperText('Preserve the existing classes, anchors and data-en/data-fa attributes. Preview the public page after editing.')
            : RichEditor::make('content')->toolbarButtons(['bold', 'italic', 'h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'link', 'codeBlock', 'undo', 'redo']);
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->helperText('Lowercase words separated by hyphens; previous URLs are retained on rename.'),
            Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch (draft only)'])->default('en')->required()->disabled(fn (?Article $record) => $record !== null)->dehydrated(),
            Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->helperText('The category must use the article language.'),
            Textarea::make('excerpt')->rows(3),
            FileUpload::make('featured_image')->label('Upload / replace featured image')->disk('public')->directory('articles')->visibility('public')
                ->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)
                ->formatStateUsing(fn ($state) => str_starts_with((string) $state, '/assets/') ? null : $state)
                ->dehydrated(fn ($state) => filled($state))->helperText('The imported image is retained unless a new image is uploaded.'),
            $content->required()->columnSpanFull(),
            TextInput::make('meta_title')->maxLength(255), Textarea::make('meta_description')->rows(3),
            TextInput::make('canonical_url')->url()->maxLength(2048)->helperText('Leave blank to use the current article URL automatically.'),
            Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
            DateTimePicker::make('published_at')->timezone(config('cms.display_timezone'))->seconds(false)->helperText('A future date schedules visibility without a background worker.'),
        ])->columns(2);
    }
    public static function table(Table $table): Table {
        return $table->columns([
            TextColumn::make('title')->searchable()->wrap(), TextColumn::make('language')->badge(),
            TextColumn::make('category.name'), TextColumn::make('status')->badge(),
            TextColumn::make('published_at')->dateTime()->sortable(),
        ])->filters([
            SelectFilter::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch']),
            SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
        ])->recordActions([EditAction::make(), DeleteAction::make()])->defaultSort('updated_at', 'desc');
    }
    public static function getPages(): array { return ['index' => ArticleResource\Pages\ListArticles::route('/'), 'create' => ArticleResource\Pages\CreateArticle::route('/create'), 'edit' => ArticleResource\Pages\EditArticle::route('/{record}/edit')]; }
}
