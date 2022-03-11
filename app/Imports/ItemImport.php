<?php

namespace App\Imports;

use App\Models\ItemModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;

class ItemImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    // public function collection( $rows)
    // {
    //     foreach ($rows as $row) {
    //         User::create([
    //             'code' => $row[0],
    //             'eng_name' => $row[1],
    //             'model' => $row[3],
    //         ]);
    //     }
    // }
    public function model(array $row)
    {
        return new ItemModel([
           'code'     => $row[0],
           'eng_name'    => $row[1], 
           'model' => $row[3],
        ]);
    }
}
