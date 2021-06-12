<?php

namespace Database\Factories;

use App\Models\App;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = App::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {



        return [
            'name' => $this->faker->name(),
            'app_id' => 'it.webmapp.'.$this->faker->unique()->slug(),
            'customerName' => $this->faker->name(),
            'maxZoom' => $this->faker->numberBetween(15,19),
            'defZoom' => $this->faker->numberBetween(12,14),
            'minZoom' => $this->faker->numberBetween(9,11),
            'fontFamilyHeader' => 'Roboto',
            'fontFamilyContent' => 'Roboto',
            'defaultFeatureColor' => $this->faker->hexColor(),
            'primary' => $this->faker->hexColor(),
            'startUrl' => '/main/explore',
            'showEditLink' => false,
            'skipRouteIndexDownload' => true,
            'poiMinRadius' => 0.5,
            'poiMaxRadius' => 1.2,
            'poiIconZoom' => 16,
            'poiIconRadius' => 1,
            'poiMinZoom' => 13,
            'poiLabelMinZoom' => 10.5,
            'showTrackRefLabel' => false,
            'user_id' => \App\Models\User::all()->random()->id,
        ];
    }
}
