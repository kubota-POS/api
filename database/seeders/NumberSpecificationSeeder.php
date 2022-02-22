<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NumberSpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
    }
}
