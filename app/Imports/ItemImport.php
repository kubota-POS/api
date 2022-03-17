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
           'code'     => $row['code'],
           'eng_name'    => $row['eng_name'], 
           'model' => $row['model'],
           'qty' => $row['qty'],
           'price' => $row['price'],
           'percentage' => $row['percentage'],
           'fix_amount' => $row['fix_amount'],
           'location' => $row['location'],
           'active' => $row['active']
        ]);
    }
    public function onError(Throwable $e)
    {
        // Handle the failures how you'd like.
    }
}
