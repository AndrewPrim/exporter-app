<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataExportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sql_file' => 'required|array',
            'typeExport'=> 'required|in:txtExport,xmlExport,csvExport'
        ];
    }
}
