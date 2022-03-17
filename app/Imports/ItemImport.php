<?php

namespace App\Imports;

use Throwable;
use App\Models\ItemModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ItemModel([
            'code' => $row['material_code'],
            'eng_name' => $row['material_name'],
            'mm_name' => null,
            'model' => $row['model'],
            'qty' => 0,
            'price' => 0,
            'percentage' => 0,
            'location' => null
        ]);
    }
    public function onError(Throwable $e)
    {
        // Handle the failures how you'd like.
    }
}
