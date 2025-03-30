<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $locales = ['en', 'fr', 'es', 'de', 'it'];
        $tags = ['mobile', 'desktop', 'web', 'api'];

        return [
            'locale' => $this->faker->randomElement($locales),
            'key' => $this->faker->unique()->lexify('key_??????'), // Generates keys like 'key_abc123'
            'value' => $this->faker->sentence,
            'tags' => json_encode($this->faker->randomElements($tags, rand(1, 3))),
        ];
    }
}
