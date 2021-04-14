<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property string $text
 * @property int $user_id
 * @property int $post_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\Post|null $post
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUserId($value)
 * @mixin \Eloquent
 */
class Comment extends Model
{
    use HasFactory;

    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * Post that have this comment.
     *
     * @return HasOne
     */
    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }

    /**
     * Comment author.
     *
     * @return HasOne
     */
    public function author(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function allow(): void
    {
        $this->update(['status' => static::STATUS_ACTIVE]);
    }

    public function disallow(): void
    {
        $this->update(['status' => static::STATUS_NOT_ACTIVE]);
    }

    /**
     * @throws \Exception
     */
    public function toggleStatus(): void
    {
        switch ($this->status) {
            case static::STATUS_NOT_ACTIVE:
                $this->allow();
                break;
            case static::STATUS_ACTIVE:
                $this->disallow();
                break;
            default:
                throw new \Exception('Not valid status.');
        }
    }

    /**
     * @throws \Exception
     */
    public function remove(): void
    {
        $this->delete();
    }
}
