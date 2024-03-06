<?php

namespace Magan\FilamentBlog\Resources\CategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Magan\FilamentBlog\Resources\CategoryResource;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
