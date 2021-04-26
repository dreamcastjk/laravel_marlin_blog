<?php

namespace App\Models;

use Eloquent;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Post
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property int|null $category_id
 * @property int|null $user_id
 * @property int $status
 * @property int $views
 * @property int $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\Category|null $category
 * @property-read Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|Post findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post whereViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Post withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin Eloquent
 */
class Post extends Model
{
    use HasFactory, Sluggable;

    const STATUS_DRAFT = 0;  // черновик
    const STATUS_PUBLIC = 1;  // черновик

    const IS_FEATURED = 1;
    const IS_STANDARD = 0;

    protected $fillable = [
        'title',
        'content',
        'date',
        'image',
        'category_id',
        'status',
        'is_featured',
    ];

    /**
     * Post category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Post author.
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Post tags.
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tags',
            'post_id',  // поле по которому связана текущая модель в таблице
            'tag_id'    // поле используемое связью, т.е. тегом.
        )->withTimestamps();
    }

    /**
     * @param $value
     * @return void
     */
    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = Carbon::createFromFormat('d/m/y', $value)->format('Y-m-d');
    }

    /**
     * @return string[][]
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ]
        ];
    }

    /**
     * @param array $fields
     * @return static
     */
    public static function add(array $fields): self
    {
        $post = new static;
        $post->fill($fields);
        $post->user_id = optional(auth()->user())->id;

        $post->save();

        return $post;
    }

    /**
     * @param array $fields
     */
    public function edit(array $fields): void
    {
        $this->fill($fields);
        $this->save();
    }

    /**
     * @throws Exception
     */
    public function remove(): void
    {
        $this->deleteImage();
        $this->tags()->detach();
        $this->delete();
    }

    /**
     * @param UploadedFile|null $image
     */
    public function uploadImage(?UploadedFile $image): void
    {
        if (!$image) {
            return;
        }

        $this->update(['image' => $this->storeImage($image)]);
    }

    /**
     * @param UploadedFile $image
     * @return string
     */
    private function storeImage(UploadedFile $image): string
    {
        $this->deleteImage();

        $fileName = Str::random(10) . '.' .$image->extension();
        $image->storeAs('uploads', $fileName);

        return $fileName;
    }

    /**
     * @return bool
     */
    private function hasImage(): bool
    {
        return !is_null($this->image);
    }

    private function deleteImage(): void
    {
        if ($this->hasImage()) {
            Storage::delete('uploads/'.$this->image);
        }
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        if (!$this->image) {
            return '/img/no-image.png';
        }

        return '/uploads/' . $this->image;
    }

    /**
     * @param int $id
     */
    public function setCategory(int $id): void
    {
        if (!$id) {
            return;
        }

        $this->update(['category_id' => $id]);
    }

    /**
     * @param array $ids
     */
    public function setTags(array $ids): void
    {
        if (!$ids) {
            return;
        }

        $this->tags()->sync($ids);
    }

    /**
     *
     */
    public function setDraft(): void
    {
        $this->update(['status' => static::STATUS_DRAFT]);
    }

    /**
     *
     */
    public function setPublic(): void
    {
        $this->update(['status' => static::STATUS_PUBLIC]);
    }

    /**
     * @param string|null $value
     */
    public function toggleStatus(?string $value): void
    {
        if (!$value) {
            $this->setDraft();

            return;
        }

        $this->setPublic();
    }

    /**
     * @return void
     */
    public function setFeatured(): void
    {
        $this->update(['is_featured' => static::IS_FEATURED]);
    }

    /**
     * @return void
     */
    public function setStandard(): void
    {
        $this->update(['is_featured' => static::IS_STANDARD]);
    }

    /**
     * @param string|null $value
     */
    public function toggleFeatured(?string $value): void
    {
        if (!$value) {
            $this->setStandard();

            return;
        }

        $this->setFeatured();
    }

    /**
     * @return string
     */
    public function getCategoryTitle(): string
    {
        return $this->category ? $this->category->title : 'Нет категории';
    }

    /**
     * @return string
     */
    public function getTagsTitles(): string
    {
        return $this->tags->isNotEmpty()
            ? implode(' ,', $this->tags->pluck('title')->all())
            : 'Нет тэгов';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getDateAttribute($value): string
    {
        return Carbon::createFromFormat('Y-m-d', $value)->format('d/m/y');
    }
}
