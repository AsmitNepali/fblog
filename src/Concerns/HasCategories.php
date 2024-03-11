<?php

namespace Magan\FilamentBlog\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Magan\FilamentBlog\Models\Category;

trait HasCategories
{
    public function categories(): Collection
    {
        return $this->categoriesRelation;
    }

    public function categoriesRelation(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
