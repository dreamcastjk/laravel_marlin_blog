<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property int $is_admin
 * @property int $status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Post[] $posts
 * @property-read int|null $posts_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const IS_ADMIN = 1;
    const IS_USER = 0;

    const STATUS_BANNED = 1;
    const STATUS_IS_ACTIVE = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Posts created by user.
     *
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Users written comments.
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @param array $fields
     * @return static
     */
    public static function add(array $fields): self
    {
        $user = new static;

        $user->fill($fields);

        $user->save();

        return $user;
    }

    public function edit(array $fields): void
    {
        $this->update($fields);
    }

    public function generatePassword(?string $password): void
    {
        if (!$password) {
            return;
        }

        $this->password = bcrypt($password);

        $this->save();
    }

    /**
     * @throws \Exception
     */
    public function remove(): void
    {
        $this->deleteAvatar();
        $this->delete();
    }

    /**
     * @param UploadedFile|null $avatar
     */
    public function uploadAvatar(?UploadedFile $avatar): void
    {
        if (!$avatar) {
            return;
        }

        $this->deleteAvatar();

        $fileName = Str::random(10) . '.' . $avatar->extension();
        $avatar->storeAs('uploads', $fileName);
        $this->avatar = $fileName;
        $this->save();
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        if (!$this->hasAvatar()) {
            return $this->getDefaultAvatar();
        }

        return '/uploads/' . $this->avatar;
    }

    /**
     * @return string
     */
    public function getDefaultAvatar(): string
    {
        return '/img/no-user-image.png';
    }

    /**
     *
     */
    public function makeAdmin(): void
    {
        $this->update(['is_admin' => static::IS_ADMIN]);
    }

    /**
     *
     */
    public function makeStandardUser(): void
    {
        $this->update(['is_admin' => static::IS_USER]);
    }

    /**
     * @param bool|null $value
     */
    public function toggleAdmin(?bool $value): void
    {
        if (!$value) {
            $this->makeStandardUser();

            return;
        }

        $this->makeAdmin();
    }

    /**
     *
     */
    public function ban(): void
    {
        $this->update(['status' => static::STATUS_BANNED]);
    }

    /**
     *
     */
    public function unban(): void
    {
        $this->update(['status' => static::STATUS_IS_ACTIVE]);
    }

    /**
     * @param bool|null $value
     */
    public function toggleBan(?bool $value): void
    {
        if (!$value) {
            $this->unban();

            return;
        }

        $this->ban();
    }

    /**
     * @return bool
     */
    public function hasAvatar(): bool
    {
        return !is_null($this->avatar);
    }

    /**
     *
     */
    public function deleteAvatar(): void
    {
        if ($this->hasAvatar()) {
            Storage::delete('uploads/'.$this->avatar);
        }
    }
}
