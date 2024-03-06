<?php

namespace Magan\FilamentBlog\Resources\CommentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Magan\FilamentBlog\Resources\CommentResource;

class CreateComment extends CreateRecord
{
    protected static string $resource = CommentResource::class;
}
