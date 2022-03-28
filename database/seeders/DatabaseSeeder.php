<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;
use App\Imports\ItemImport;
use App\Imports\PriceImport;
use App\Models\ItemModel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;

class DatabaseSeeder extends Seeder
{
    public function __construct() {
    }

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
        
        $this->command->info("Number Specification seeding completed successfully");

        \App\Models\CategoryModel::factory()->create([
            "name" => "kubota"
        ]);

        // $this->command->info("Import item category for kubota seeding completed successfully");

        // $path = storage_path('../database/seeders/xlsx/items.xlsx');
        // Excel::import(new ItemImport, $path);

        // $this->command->info("Import item of kubota seeding completed successfully");
    }
}
