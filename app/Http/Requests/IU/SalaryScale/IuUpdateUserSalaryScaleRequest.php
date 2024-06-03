<?php

namespace App\Http\Requests\IU\SalaryScale;

use Illuminate\Foundation\Http\FormRequest;

class IuUpdateUserSalaryScaleRequest extends FormRequest
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
            'discounted_country_id' => [
                'required',
                'numeric',
                'exists:discounted_countries,id',
            ],
            'discounted_country_range_id' => [
                'required_with:discounted_country_id',
                'numeric',
                'exists:discounted_country_ranges,id',
            ],
            'declaration' => [
                'prohibited',
            ],
        ];
    }

    public function messages()
    {
        return [
            'declaration.prohibited' => 'The declaration cannot be updated.',
        ];
    }
}
