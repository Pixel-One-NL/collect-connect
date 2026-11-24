<?php

declare(strict_types=1);

namespace App\Filament\Resources\Articles\Schemas;

use App\Models\Article;
use App\Support\ImageVariant;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(255)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true),
            Toggle::make('is_published')->default(false),
            DateTimePicker::make('published_at')->helperText('Leave empty to publish immediately when enabled.'),
            TextInput::make('meta_title')->maxLength(255),
            Textarea::make('meta_description')->maxLength(500)->helperText('Also used as the excerpt on the blog overview.'),
            Section::make('Cover image')
                ->schema([
                    ImageEntry::make('current_cover')
                        ->label('Current cover')
                        ->state(fn (?Article $record): ?string => $record?->imageUrl(ImageVariant::Medium))
                        ->visible(fn (?Article $record): bool => $record?->hasCloudflareImage() ?? false),
                    FileUpload::make('cover_upload')
                        ->label(fn (?Article $record): string => $record?->hasCloudflareImage() ? 'Replace cover' : 'Upload cover')
                        ->image()
                        ->maxSize(10240)
                        ->disk('local')
                        ->directory('article-covers')
                        ->helperText('Shown on the blog overview and at the top of the article. Landscape (2:1) works best.'),
                    Toggle::make('remove_cover')
                        ->label('Remove current cover')
                        ->default(false)
                        ->visible(fn (?Article $record): bool => $record?->hasCloudflareImage() ?? false),
                ])
                ->columnSpanFull(),
            RichEditor::make('content')->fileAttachments(false)->required()->columnSpanFull(),
        ]);
    }
}
