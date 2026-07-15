<?php

namespace Database\Factories;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeBaseArticleFactory extends Factory
{
    protected $model = KnowledgeBaseArticle::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(4),
            'content' => fake()->paragraphs(3, true),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'category' => fake()->randomElement(['technical', 'billing', 'support']),
        ];
    }
}
