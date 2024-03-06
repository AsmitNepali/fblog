<?php

namespace Magan\FilamentBlog\Resources\NewsletterResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Magan\FilamentBlog\Resources\NewsletterResource;

class ListNewsletters extends ListRecords
{
    protected static string $resource = NewsletterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
