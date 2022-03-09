<?php

namespace App\Imports;

use App\Models\ItemModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;

class ItemImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
         Validator::make($rows->toArray(), [
             'code' => 'required',
             'eng_name' => 'required',
             'model' => 'required',
         ])->validate();
  
        foreach ($rows as $row) {
            User::create([
                'code' => $row[0],
                'eng_name' => $row[1],
                'model' => $row[3],
            ]);
        }
    }
}
