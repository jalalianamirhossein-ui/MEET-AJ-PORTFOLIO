<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | \UnitEnum | null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category')
                ->description('Used to group articles. Language cannot change after articles are assigned.')
                ->schema([
                    TextInput::make('name')->required()->maxLength(255),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(180)
                        ->live(onBlur: true)
                        ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                        ->helperText('If Accent color is empty, the public palette for this slug is used.'),
                    Select::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch'])->default('en')->required(),
                    TextInput::make('translation_key')->disabled()->dehydrated(false)->helperText('Assigned automatically. Unique per language.'),
                    ColorPicker::make('accent_color')
                        ->label('Accent color')
                        ->helperText('Used for article cards, badges and category accents on the public site.')
                        ->hex()
                        ->nullable()
                        ->live()
                        ->regex(Category::ACCENT_PATTERN)
                        ->dehydrateStateUsing(fn (?string $state): ?string => Category::normalizeAccentColor($state))
                        ->placeholder(fn (Get $get, ?Category $record): string => Category::accentColorForSlug((string) ($get('slug') ?: $record?->slug ?: 'others')))
                        ->extraInputAttributes([
                            'x-on:input' => 'setState($event.target.value)',
                            'x-on:change' => 'commitState()',
                        ])
                        ->suffixAction(
                            Action::make('clearAccentColor')
                                ->label('Reset')
                                ->icon(Heroicon::OutlinedXMark)
                                ->tooltip('Clear and use the slug palette')
                                ->action(fn (Set $set) => $set('accent_color', null))
                        ),
                    Placeholder::make('accent_preview')
                        ->label('Preview')
                        ->content(function (Get $get, ?Category $record): HtmlString {
                            $fallback = Category::accentColorForSlug((string) ($get('slug') ?: $record?->slug ?: 'others'));
                            $stored = Category::normalizeAccentColor($get('accent_color'));
                            $fallbackJs = Js::from($fallback);
                            $typedJs = Js::from($stored ?? '');

                            return new HtmlString(<<<HTML
<div
    class="meetaj-accent-live"
    x-data="{
        fallback: {$fallbackJs},
        typed: {$typedJs},
        init() {
            const root = this.\$el.closest('.fi-section, .fi-sc-section, .fi-modal, .fi-slide-over') || this.\$el.parentElement;
            const input = root ? root.querySelector('.fi-fo-color-picker input.fi-input') : null;
            const sync = () => { this.typed = input && input.value ? input.value : ''; };
            if (! input) {
                return;
            }
            input.addEventListener('input', sync);
            input.addEventListener('change', sync);
            input.addEventListener('blur', sync);
            const wrap = input.closest('[x-data]');
            if (wrap && window.Alpine) {
                Alpine.effect(() => {
                    const data = Alpine.\$data(wrap);
                    if (data && Object.prototype.hasOwnProperty.call(data, 'state')) {
                        this.typed = data.state || '';
                    }
                });
            }
            sync();
        },
        get color() {
            const value = String(this.typed || '').trim();
            return /^#[0-9A-Fa-f]{6}$/.test(value) ? ('#' + value.slice(1).toLowerCase()) : this.fallback;
        },
        get note() {
            const value = String(this.typed || '').trim();
            return /^#[0-9A-Fa-f]{6}$/.test(value) ? 'Selected color' : 'Slug fallback';
        }
    }"
>
    <span class="meetaj-category-chip meetaj-accent-preview" :style="'--meetaj-topic: ' + color">
        <i class="meetaj-category-dot" aria-hidden="true"></i>
        <span class="meetaj-accent-hex" x-text="color.toUpperCase()"></span>
    </span>
    <span class="meetaj-accent-source" x-text="note"></span>
</div>
HTML);
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->html()
                    ->formatStateUsing(fn (string $state, Category $record): HtmlString => new HtmlString($record->accentChipHtml($state))),
                TextColumn::make('accent')
                    ->label('Accent')
                    ->state(fn (Category $record): string => $record->accentColor())
                    ->badge()
                    ->extraAttributes(fn (Category $record): array => [
                        'class' => 'meetaj-category-hex',
                        'style' => '--meetaj-topic: '.$record->accentColor(),
                    ]),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('language')->badge()->color('gray')->sortable(),
                TextColumn::make('articles_count')->counts('articles')->label('Articles'),
            ])
            ->filters([
                SelectFilter::make('language')->options(['en' => 'English', 'fa' => 'فارسی', 'de' => 'Deutsch']),
            ])
            ->recordActions([
                EditAction::make()->color('gray'),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->requiresConfirmation(),
            ])
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Create a category before assigning it to an article.')
            ->emptyStateIcon(Heroicon::OutlinedSquares2x2)
            ->emptyStateActions([CreateAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => CategoryResource\Pages\ManageCategories::route('/')];
    }
}
