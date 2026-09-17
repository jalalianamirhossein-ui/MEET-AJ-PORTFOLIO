<?php

namespace App\Filament\Resources;

use App\Models\Article;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'excerpt', 'meta_title'];
    }

    public static function form(Schema $schema): Schema
    {
        $content = $schema->getRecord()?->presentation
            ? Textarea::make('content')->label('Article HTML')->rows(22)->helperText('Keep existing classes, heading ids, code blocks, and data-en / data-fa attributes. Preview the public URL after saving.')
            : RichEditor::make('content')->toolbarButtons(['bold', 'italic', 'h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'link', 'codeBlock', 'undo', 'redo']);

        return $schema->components([
            Section::make('Identity')
                ->description('Slug changes keep the previous public URL as a 301 redirect.')
                ->icon(Heroicon::OutlinedIdentification)
                ->columns(2)
                ->schema([
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->required()->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->helperText('Lowercase words separated by hyphens.'),
                    Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch (draft only)'])->default('en')->required()->disabled(fn (?Article $record) => $record !== null)->dehydrated(),
                    Select::make('category_id')->relationship('category', 'name')->searchable()->preload()->helperText('Category language must match the article language.'),
                    Select::make('tags')->relationship('tags', 'name')->multiple()->preload()->searchable()->columnSpanFull(),
                    Textarea::make('excerpt')->rows(3)->columnSpanFull(),
                ]),
            Section::make('Featured image')
                ->icon(Heroicon::OutlinedPhoto)
                ->schema([
                    FileUpload::make('featured_image')
                        ->label('Upload / replace featured image')
                        ->disk('public')
                        ->directory('articles')
                        ->visibility('public')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->formatStateUsing(fn ($state) => str_starts_with((string) $state, '/assets/') ? null : $state)
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText('Imported images under /assets/ stay in place unless you upload a replacement. JPEG, PNG or WebP, max 5 MB.'),
                ]),
            Section::make('Body')
                ->icon(Heroicon::OutlinedDocumentText)
                ->schema([
                    $content->required()->columnSpanFull(),
                ]),
            Section::make('SEO')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')->maxLength(255),
                    Textarea::make('meta_description')->rows(3),
                    TextInput::make('canonical_url')->url()->maxLength(2048)->helperText('Leave blank to use the current public article URL.'),
                ]),
            Section::make('Publishing')
                ->icon(Heroicon::OutlinedCalendar)
                ->columns(2)
                ->schema([
                    Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                    DateTimePicker::make('published_at')->timezone(config('cms.display_timezone'))->seconds(false)->helperText('A future date schedules visibility without a queue worker. German must remain draft.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->wrap()->limit(48),
                TextColumn::make('language')->badge()->sortable(),
                TextColumn::make('category.name')->placeholder('—')->toggleable(),
                TextColumn::make('tags.name')->badge()->separator(',')->toggleable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray')->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable()->placeholder('—'),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch']),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                SelectFilter::make('category')->relationship('category', 'name'),
                SelectFilter::make('tags')->relationship('tags', 'name'),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Article $record): string => $record->path())
                    ->openUrlInNewTab()
                    ->visible(fn (Article $record): bool => $record->status === 'published' && $record->published_at?->lte(now())),
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publish selected')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $published = 0;
                            $skipped = 0;
                            $records->each(function (Article $article) use (&$published, &$skipped): void {
                                if ($article->language === 'de') {
                                    $skipped++;

                                    return;
                                }
                                try {
                                    $article->status = 'published';
                                    $article->published_at ??= now();
                                    $article->save();
                                    $published++;
                                } catch (ValidationException) {
                                    $skipped++;
                                }
                            });
                            Notification::make()->title("Published {$published} article(s).")->body($skipped ? "{$skipped} skipped (German or validation)." : null)->success()->send();
                        }),
                    BulkAction::make('draft')
                        ->label('Move to draft')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Article $article): void {
                                $article->status = 'draft';
                                $article->save();
                            });
                            Notification::make()->title('Selected articles are now drafts.')->success()->send();
                        }),
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('No articles yet')
            ->emptyStateDescription('Import the original 23 HTML articles or create a draft. Published English and Persian URLs stay public.')
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->emptyStateActions([CreateAction::make()])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ArticleResource\Pages\ListArticles::route('/'),
            'create' => ArticleResource\Pages\CreateArticle::route('/create'),
            'edit' => ArticleResource\Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
