<?php

namespace App\Exports;

use App\Models\BerkasPersuratan;
use Maatwebsite\Excel\Concerns\FromCollection;

class BerkasPersuratanExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return BerkasPersuratan::all();
    }
}
