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
