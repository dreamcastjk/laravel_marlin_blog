<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Comment extends Model
{
    use HasFactory;

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
}
