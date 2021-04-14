<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
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
 * @mixin \Eloquent
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
    ];

    /**
     * Post category.
     *
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(Category::class);
    }

    /**
     * Post author.
     *
     * @return HasOne
     */
    public function author(): HasOne
    {
        return $this->hasOne(User::class);
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
        );
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
        $post->user_id = auth()->user()->id;

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
     * @throws \Exception
     */
    public function remove(): void
    {
        Storage::delete('uploads/'.$this->image);
        $this->delete();
    }

    /**
     * @param UploadedFile $image
     */
    public function uploadImage(UploadedFile $image): void
    {
        if (!$image) {
            return;
        }

        Storage::delete('uploads/'.$this->image);
        $fileName = Str::random(10) . '.' .$image->extension();
        $image->storeAs('uploads', $fileName);

        $this->update(['image' => $fileName]);
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
     * @param int|null $value
     */
    public function toggleStatus(?int $value): void
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
     * @param int|null $value
     */
    public function toggleFeatured(?int $value): void
    {
        if (!$value) {
            $this->setStandard();

            return;
        }

        $this->setFeatured();
    }
}
