<?php

namespace App\Filament\Resources;

use App\Models\HomepageContent;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomepageContentResource extends Resource
{
    protected static ?string $model = HomepageContent::class;
    protected static ?string $recordTitleAttribute = 'label';
    protected static string | \UnitEnum | null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 0;
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedHome;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Homepage section')
                ->description('Each record controls one homepage section. Keep the key stable because the public page uses it to load the content.')
                ->columns(2)
                ->schema([
                    TextInput::make('key')->required()->maxLength(80)->regex('/^[a-z0-9-]+$/')->disabled(fn (?HomepageContent $record): bool => $record !== null)->dehydrated(),
                    TextInput::make('label')->required()->maxLength(120),
                    TextInput::make('sort_order')->numeric()->integer()->minValue(0)->required()->helperText('Lower numbers render first.'),
                    Toggle::make('is_published')->label('Published on homepage')->default(true),
                ]),
            Section::make('Common fields')
                ->description('These fields are used by Hero, About, Skills, Resume and Contact. Leave unused fields blank.')
                ->columns(2)
                ->schema([
                    TextInput::make('content.title_en')->label('Title (English)'),
                    TextInput::make('content.title_fa')->label('Title (Persian)'),
                    TextInput::make('content.intro_en')->label('Intro (English)')->columnSpanFull(),
                    TextInput::make('content.intro_fa')->label('Intro (Persian)')->columnSpanFull(),
                    Textarea::make('content.body_en')->label('Body (English)')->rows(3),
                    Textarea::make('content.body_fa')->label('Body (Persian)')->rows(3),
                ]),
            Section::make('Advanced structured data')
                ->description('Edit repeatable facts, skills, education, experience, contact cards, links and other section-specific values as JSON. The default content is already populated.')
                ->schema([
                    Textarea::make('content_json')->label('Section JSON')->rows(22)->required()->formatStateUsing(fn ($state, ?HomepageContent $record): string => json_encode($record?->content ?? $state ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))->dehydrated(true)->helperText('Valid JSON is required. Use the existing structure as a template; values can be bilingual with *_en and *_fa keys.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('label')->label('Section')->searchable()->sortable(),
                TextColumn::make('key')->badge()->sortable(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('is_published')->label('Published')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')->color(fn (bool $state): string => $state ? 'success' : 'gray')->sortable(),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()->requiresConfirmation()])
            ->toolbarActions([DeleteBulkAction::make()->requiresConfirmation()])
            ->emptyStateHeading('No homepage sections yet')
            ->emptyStateDescription('The default homepage sections are created automatically on the next public visit.')
            ->emptyStateActions([CreateAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => HomepageContentResource\Pages\ListHomepageContents::route('/'),
            'create' => HomepageContentResource\Pages\CreateHomepageContent::route('/create'),
            'edit' => HomepageContentResource\Pages\EditHomepageContent::route('/{record}/edit'),
        ];
    }
}
