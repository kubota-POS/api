<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for($x=0; $x<=9; $x++) {
            \App\Models\NumberSpecificationModel::factory()->create([
                "set_number" => $x
            ]);
        }

        \App\Models\CategoryModel::factory()->create([
            "name" => "kubota"
        ]);

        for($x=1; $x<1000; $x++) {
            \App\Models\ItemModel::factory()->create([
                "category_id" => 1,
                "code" => "CODE_" . $x,
                "eng_name" => "ENG_NAME_" . $x,
                "mm_name" => "MM_NAME_" . $x,
                "model" => "MODEL_" . $x,
                "qty" => 10 * $x,
                "price" => 100 * $x,
                "percentage" => 20,
            ]); 
        }

    }
}
