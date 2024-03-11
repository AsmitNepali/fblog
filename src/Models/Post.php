<?php

namespace Magan\FilamentBlog\Models;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Magan\FilamentBlog\Enums\PostStatus;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'sub_title',
        'body',
        'status',
        'published_at',
        'scheduled_for',
        'cover_photo_path',
        'photo_alt_text',
        'user_id',
    ];

    protected $dates = [
        'published_at',
        'scheduled_for',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'published_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'status' => PostStatus::class,
        'user_id' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seoDetail()
    {
        return $this->hasOne(SeoDetail::class);
    }

    public function scopePublished(Builder $query)
    {
        return $query->where('status', PostStatus::PUBLISHED);
    }

    public function formattedPublishedDate()
    {
        return $this->published_at?->format('d M Y');
    }

    public function isScheduled()
    {
        return $this->status === PostStatus::SCHEDULED;
    }

    public function relatedPosts($take = 3)
    {
        return $this->whereHas('categories', function ($query) {
            $query->whereIn('categories.id', $this->categories->pluck('id'))
                ->whereNotIn('posts.id', [$this->id]);
        })->with('user')->take($take)->get();
    }

    public static function getForm()
    {
        return [
            Section::make('Blog Details')
                ->schema([
                    Fieldset::make('Titles')
                        ->schema([
                            Select::make('category_id')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->relationship('categories', 'name')
                                ->columnSpanFull(),

                            TextInput::make('title')
                                ->live()->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug',
                                    Str::slug($state)))
                                ->required()
                                ->unique('posts', 'title', null, 'id')
                                ->maxLength(255),

                            TextInput::make('slug')
                                ->readOnly()
                                ->maxLength(255),

                            Textarea::make('sub_title')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Select::make('tag_id')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->relationship('tags', 'title')
                                ->columnSpanFull(),
                        ]),
                    TiptapEditor::make('body')
                        ->extraInputAttributes(['style' => 'min-height: 24rem;'])
                        ->required()
                        ->columnSpanFull(),
                    Fieldset::make('Feature Image')
                        ->schema([
                            FileUpload::make('cover_photo_path')
                                ->label('Cover Photo')
                                ->directory('public/blog-feature-images')
                                ->hint('This cover image is used in your blog post as a feature image. Recommended image size 1200 X 628')
                                ->image()
                                ->preserveFilenames()
                                ->imageEditor()
                                ->maxSize(1024 * 1024 * 3)
                                ->required(),
                            TextInput::make('photo_alt_text')->required(),
                        ]),

                    Fieldset::make('Status')
                        ->schema([
                            Select::make('status')
                                ->options(PostStatus::class)
                                ->live()
                                ->required(),

                            DateTimePicker::make('scheduled_for')
                                ->visible(function ($get) {
                                    return $get('status') === PostStatus::SCHEDULED->value;
                                })
                                ->required(function ($get) {
                                    return $get('status') === PostStatus::SCHEDULED->value;
                                })
                                ->native(false),

                            DateTimePicker::make('published_at')
                                ->visible(function ($get) {
                                    return $get('status') === PostStatus::PUBLISHED->value;
                                })
                                ->hidden()
                                ->native(false),
                        ]),
                    Select::make('user_id')
                        ->relationship('user', 'name')
                        ->required(),
                ]),
        ];
    }
}
