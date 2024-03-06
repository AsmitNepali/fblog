<?php

namespace Magan\FilamentBlog\Resources\CategoryResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Magan\FilamentBlog\Models\Category;
use Magan\FilamentBlog\Resources\CategoryResource;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    public function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->slideOver()
                ->form(Category::getForm()),
        ];
    }
}
