<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class GenericRowsImport implements ToArray
{
    public function array(array $array)
    {
        // No-op: la lectura se hace con Excel::toArray().
    }
}
