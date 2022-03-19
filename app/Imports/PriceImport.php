<?php

namespace App\Imports;

use Throwable;
use App\Models\ItemPriceModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PriceImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ItemPriceModel([
            'material_code' => $row['material_code'],
            'price' => $row['price'],
        ]);
    }
    
    public function onError(Throwable $e)
    {
        // Handle the failures how you'd like.
    }
}
