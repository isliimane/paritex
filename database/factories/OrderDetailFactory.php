<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrderDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_id'              => Order::inRandomOrder()->first()->id,
            'product_id'            => Product::all()->random()->id,
            'price'                 => $this->faker->randomElement(['500', '1450', '1670','2630','4456','260']),
            'pickup_hub_id'         => $this->faker->randomElement([null, null, 1,2,3,null]),
        ];
    }
}
