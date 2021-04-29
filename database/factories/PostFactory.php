<?php

namespace Database\Factories;

use Exception;
use Carbon\Carbon;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * @return array
     *
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->text,
            'category_id' => Category::inRandomOrder()->first()->id,
            'status' => random_int(Post::STATUS_DRAFT, Post::STATUS_PUBLIC),
            'views' => $this->faker->numberBetween(0, 5000),
            'is_featured' => random_int(Post::IS_STANDARD, Post::IS_FEATURED),
            'date' => Carbon::today()->addDays(random_int(1, 5))->format('d/m/y'),
            'description' => $this->faker->text,
        ];
    }
}
