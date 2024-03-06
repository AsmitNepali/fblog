<?php

namespace Magan\FilamentBlog\Models;

use App\Enums\PostStatus;
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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'user_id',
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

    public function categories(): BelongsToMany
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
                                ->live()->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                ->required()
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
