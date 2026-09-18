<?php

namespace App\Filament\Resources;

use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'short_description', 'seo_title'];
    }

    public static function form(Schema $schema): Schema
    {
        $pair = fn (string $en, string $fa) => [
            TextInput::make($en)->label('English')->required()->columnSpan(1),
            TextInput::make($fa)->label('Persian')->columnSpan(1),
        ];

        return $schema->components([
            Section::make('General')
                ->icon(Heroicon::OutlinedIdentification)
                ->columns(2)
                ->schema([
                    TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    TextInput::make('slug')->required()->maxLength(180)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->helperText('Lowercase words separated by hyphens. Unique per language.'),
                    Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch (draft only)'])->default('en')->required()->disabled(fn (?Service $record) => $record !== null)->dehydrated(),
                    TextInput::make('translation_key')->label('Translation key')->helperText('Leave blank to generate. Pairs EN/FA/DE rows of the same service.')->maxLength(36),
                    TextInput::make('sort_order')->numeric()->default(0)->required()->helperText('Homepage order. Lower numbers first.'),
                    Toggle::make('show_in_catalog')->label('Show on homepage catalog')->default(true)->helperText('Keeps the service page and data even when hidden from the homepage grid.'),
                ]),
            Section::make('Content')
                ->icon(Heroicon::OutlinedDocumentText)
                ->schema([
                    Textarea::make('short_description')->label('Short description (cards)')->rows(3)->columnSpanFull(),
                    Textarea::make('description')->label('Hero description')->rows(3)->columnSpanFull(),
                    Textarea::make('content')->label('Main description')->rows(6)->columnSpanFull(),
                    Repeater::make('features')->label('Features / scope')->columns(2)->schema($pair('en', 'fa'))->default([])->columnSpanFull(),
                    Repeater::make('process')->label('Process / timeline')->columns(2)->schema([
                        TextInput::make('en')->label('Phase (EN)')->required(),
                        TextInput::make('fa')->label('Phase (FA)'),
                        TextInput::make('duration_en')->label('Duration (EN)'),
                        TextInput::make('duration_fa')->label('Duration (FA)'),
                    ])->default([])->columnSpanFull(),
                    Repeater::make('faq')->label('FAQ')->schema([
                        TextInput::make('question_en')->label('Question (EN)')->required()->columnSpanFull(),
                        TextInput::make('question_fa')->label('Question (FA)')->columnSpanFull(),
                        Textarea::make('answer_en')->label('Answer (EN)')->rows(2)->required()->columnSpanFull(),
                        Textarea::make('answer_fa')->label('Answer (FA)')->rows(2)->columnSpanFull(),
                    ])->default([])->columnSpanFull(),
                ]),
            Section::make('Pricing')
                ->icon(Heroicon::OutlinedBanknotes)
                ->columns(2)
                ->schema([
                    Select::make('price_type')->options([
                        'fixed' => 'Fixed',
                        'starting_from' => 'Starting from',
                        'custom_quote' => 'Request a quote',
                    ])->required()->default('fixed'),
                    TextInput::make('price')->numeric()->minValue(0)->step(0.01)->helperText('Leave empty for custom quote.'),
                    TextInput::make('price_currency')->maxLength(8)->default('AED')->required(),
                    TextInput::make('price_label')->maxLength(255)->helperText('Optional badge, e.g. Fixed Price.'),
                ]),
            Section::make('Media')
                ->icon(Heroicon::OutlinedPhoto)
                ->schema([
                    FileUpload::make('featured_image')
                        ->disk('public')
                        ->directory('services')
                        ->visibility('public')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->formatStateUsing(fn ($state) => str_starts_with((string) $state, '/assets/') ? null : $state)
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText('Optional. JPEG, PNG or WebP, max 5 MB. Homepage cards use the Bootstrap icon when no image is set.'),
                    TextInput::make('presentation.icon')->label('Icon class')->helperText('Bootstrap Icons class, e.g. bi bi-diagram-3.')->maxLength(80),
                ]),
            Section::make('SEO')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->collapsed()
                ->schema([
                    TextInput::make('seo_title')->maxLength(255),
                    Textarea::make('seo_description')->rows(3),
                    TextInput::make('og_title')->maxLength(255),
                    Textarea::make('og_description')->rows(3),
                ]),
            Section::make('Publishing')
                ->icon(Heroicon::OutlinedCalendar)
                ->columns(2)
                ->schema([
                    Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->default('draft')->required(),
                    DateTimePicker::make('published_at')->timezone(config('cms.display_timezone'))->seconds(false)->helperText('German must remain draft. Future dates stay private until due.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->wrap()->limit(40),
                TextColumn::make('language')->badge()->sortable(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray')->sortable(),
                TextColumn::make('price')->sortable()->placeholder('—')->formatStateUsing(fn ($state, Service $record) => $record->displayPrice('en')),
                TextColumn::make('price_currency')->label('Currency')->sortable()->toggleable(),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('published_at')->dateTime()->sortable()->placeholder('—'),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch']),
                SelectFilter::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                SelectFilter::make('price_type')->options([
                    'fixed' => 'Fixed',
                    'starting_from' => 'Starting from',
                    'custom_quote' => 'Custom quote',
                ]),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Service $record): string => $record->path())
                    ->openUrlInNewTab()
                    ->visible(fn (Service $record): bool => $record->status === 'published' && $record->published_at?->lte(now()) && $record->language !== 'de'),
                EditAction::make(),
                ReplicateAction::make()
                    ->excludeAttributes(['slug', 'status', 'published_at'])
                    ->beforeReplicaSaved(function (Service $replica): void {
                        $replica->slug = $replica->slug.'-copy';
                        $replica->status = 'draft';
                        $replica->published_at = null;
                        $replica->translation_key = (string) Str::uuid();
                    }),
                Action::make('publish')
                    ->label('Publish')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (Service $record): bool => $record->status !== 'published')
                    ->action(function (Service $record): void {
                        try {
                            $record->status = 'published';
                            $record->published_at ??= now();
                            $record->save();
                            Notification::make()->title('Service published')->success()->send();
                        } catch (ValidationException $exception) {
                            Notification::make()->title('Could not publish')->body($exception->getMessage())->danger()->send();
                        }
                    }),
                Action::make('unpublish')
                    ->label('Unpublish')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->requiresConfirmation()
                    ->visible(fn (Service $record): bool => $record->status === 'published')
                    ->action(function (Service $record): void {
                        $record->status = 'draft';
                        $record->save();
                        Notification::make()->title('Service moved to draft')->success()->send();
                    }),
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
                            $records->each(function (Service $service) use (&$published, &$skipped): void {
                                if ($service->language === 'de') {
                                    $skipped++;

                                    return;
                                }
                                try {
                                    $service->status = 'published';
                                    $service->published_at ??= now();
                                    $service->save();
                                    $published++;
                                } catch (ValidationException) {
                                    $skipped++;
                                }
                            });
                            Notification::make()->title("Published {$published} service(s).")->body($skipped ? "{$skipped} skipped." : null)->success()->send();
                        }),
                    BulkAction::make('draft')
                        ->label('Unpublish selected')
                        ->icon(Heroicon::OutlinedPencilSquare)
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): void {
                            $records->each(function (Service $service): void {
                                $service->status = 'draft';
                                $service->save();
                            });
                            Notification::make()->title('Selected services are now drafts.')->success()->send();
                        }),
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('No services yet')
            ->emptyStateDescription('Import the original six HTML service pages or create a draft. Only published English services appear on the homepage.')
            ->emptyStateIcon(Heroicon::OutlinedBriefcase)
            ->emptyStateActions([CreateAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => ServiceResource\Pages\ListServices::route('/'),
            'create' => ServiceResource\Pages\CreateService::route('/create'),
            'edit' => ServiceResource\Pages\EditService::route('/{record}/edit'),
        ];
    }
}
