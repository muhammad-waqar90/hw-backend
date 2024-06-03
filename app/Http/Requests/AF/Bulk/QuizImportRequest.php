<?php

namespace App\Http\Requests\AF\Bulk;

use Illuminate\Foundation\Http\FormRequest;

class QuizImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'duration'      => [
                'required',
                'integer',
                'min:10',
            ], // quiz | exam seconds
            'file'          => [
                'required',
                'max:5120',
                'mimes:xlsx,xls',
            ], // 5MB
            'sample_size'   => [
                'required',
                'integer',
                'min:4',
                'is_divisible_by:4',
            ], // quiz | exam number of questions to attempt
            'price'         => [
                'present',
                'nullable',
                'numeric',
                'between:0,99.99',
            ], // exam price
        ];
    }
}
