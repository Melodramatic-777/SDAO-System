<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ScholarsTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        // Empty template data
        return [];
    }

    public function headings(): array
    {
        return [
            'student_no',
            'email',
            'last_name',
            'first_name',
            'middle_name',
            'suffix',
            'birthday',
            'school',
            'program',
            'scholarship_type'
        ];
    }
}
