<?php

namespace App\Filament\Resources;

use App\Models\Testimonial;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;
    protected static ?string $recordTitleAttribute = 'author_name';
    protected static string | \UnitEnum | null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 4;
    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageContent() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identity')
                ->columns(2)
                ->schema([
                    TextInput::make('author_name')->label('Name')->required()->maxLength(255),
                    TextInput::make('sort_order')->label('Display order')->numeric()->integer()->minValue(0)->default(fn (?Testimonial $record): int => $record?->sort_order ?? (((int) Testimonial::max('sort_order')) + 10))->required()->helperText('Lower numbers appear first.'),
                    TextInput::make('role_en')->label('Role (English)')->maxLength(255),
                    TextInput::make('role_fa')->label('Role (Persian)')->maxLength(255),
                    TextInput::make('company_en')->label('Company (English)')->maxLength(255),
                    TextInput::make('company_fa')->label('Company (Persian)')->maxLength(255),
                ]),
            Section::make('Quote')
                ->columns(2)
                ->schema([
                    Textarea::make('quote_en')->label('Quote (English)')->required()->rows(6),
                    Textarea::make('quote_fa')->label('Quote (Persian)')->rows(6),
                ]),
            Section::make('Presentation')
                ->columns(2)
                ->schema([
                    FileUpload::make('avatar')->label('Avatar')->disk('public')->directory('testimonials')->visibility('public')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(2048)->formatStateUsing(fn ($state) => str_starts_with((string) $state, '/assets/') ? null : $state)->dehydrated(fn ($state) => filled($state))->helperText('Optional. Existing assets under /assets/ stay in place unless replaced.'),
                    Toggle::make('is_published')->label('Show publicly')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                TextColumn::make('author_name')->label('Name')->searchable()->sortable(),
                TextColumn::make('role_en')->label('Role')->searchable()->wrap()->limit(32),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('is_published')->label('Published')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')->color(fn (bool $state): string => $state ? 'success' : 'gray')->sortable(),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()->requiresConfirmation()])
            ->toolbarActions([DeleteBulkAction::make()->requiresConfirmation()])
            ->emptyStateHeading('No testimonials yet')
            ->emptyStateDescription('Add a testimonial to show it in the homepage slider.')
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleLeftRight)
            ->emptyStateActions([CreateAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => TestimonialResource\Pages\ListTestimonials::route('/'),
            'create' => TestimonialResource\Pages\CreateTestimonial::route('/create'),
            'edit' => TestimonialResource\Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
