<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    /**
     * @param string $email
     * @return static
     */
    public static function add(string $email): self
    {
        $sub = new static;
        $sub->email = $email;
        $sub->token = Str::random(100);
        $sub->save();

        return $sub;
    }

    /**
     * @throws \Exception
     */
    public function remove(): void
    {
        $this->delete();
    }
}
