<?php

namespace App\Imports;

use Throwable;
use App\Models\SecondItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SecondItemImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use Importable, SkipsErrors;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new SecondItem([
            'm_code'     => $row['m_code'],
            'm_name'    => $row['m_name'], 
            'm_photo' => $row['m_photo'],
            'm_qty'     => $row['m_qty'],
            'price_code'    => $row['price_code'], 
            'sell_percentage' => $row['sell_percentage'],
            'location'     => $row['location'],
            'c_date'    => $row['c_date'], 
            'm_active' => $row['m_active'],
        ]);
    }
    public function onError(Throwable $e)
    {
        // Handle the failures how you'd like.
    }
}
