<?php

namespace App\Http\Requests\IU\SalaryScale;

use Illuminate\Foundation\Http\FormRequest;

class IuUpdateEnableSalaryScaleFlagRequest extends FormRequest
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
            'enable_salary_scale' => [
                'required',
                'boolean',
            ],
        ];
    }
}
