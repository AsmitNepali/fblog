<?php

namespace Magan\FilamentBlog\Resources\SeoDetailResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Magan\FilamentBlog\Resources\SeoDetailResource;

class EditSeoDetail extends EditRecord
{
    protected static string $resource = SeoDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
