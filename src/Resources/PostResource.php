<?php

namespace Magan\FilamentBlog\Resources;

use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Magan\FilamentBlog\Enums\PostStatus;
use Magan\FilamentBlog\Models\Post;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Post::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->description(function (Post $record) {
                        return Str::limit($record->sub_title, 40);
                    })
                    ->searchable()->limit(20),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function ($state) {
                        return $state->getColor();
                    }),
                Tables\Columns\ImageColumn::make('cover_photo_path')->label('Cover Page'),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Post')
                ->schema([
                    Fieldset::make('General')
                        ->schema([
                            TextEntry::make('title'),
                            TextEntry::make('slug'),
                            TextEntry::make('sub_title'),
                        ]),
                    Fieldset::make('Publish Information')
                        ->schema([
                            TextEntry::make('status')
                                ->badge()->color(function ($state) {
                                    return $state->getColor();
                                }),
                            TextEntry::make('published_at')->visible(function (Post $record) {
                                return $record->status === PostStatus::PUBLISHED->value;
                            }),

                            TextEntry::make('scheduled_for')->visible(function (Post $record) {
                                return $record->status === PostStatus::SCHEDULED->value;
                            }),
                        ]),
                    Fieldset::make('Description')
                        ->schema([
                            TextEntry::make('body')
                                ->html()
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \Magan\FilamentBlog\Resources\PostResource\RelationManagers\SeoDetailRelationManager::class,
            \Magan\FilamentBlog\Resources\PostResource\RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Magan\FilamentBlog\Resources\PostResource\Pages\ListPosts::route('/'),
            'create' => \Magan\FilamentBlog\Resources\PostResource\Pages\CreatePost::route('/create'),
            'edit' => \Magan\FilamentBlog\Resources\PostResource\Pages\EditPost::route('/{record}/edit'),
            'view' => \Magan\FilamentBlog\Resources\PostResource\Pages\ViewPost::route('/{record}'),
        ];
    }
}
