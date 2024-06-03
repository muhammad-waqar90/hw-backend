<?php

namespace App\Http\Requests\AF\Adverts;

use Illuminate\Foundation\Http\FormRequest;

class AfAdvertSortingRequest extends FormRequest
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
            'data' => [
                'required',
                'array',
                'min:1',
            ],
            'data.*.id' => [
                'integer',
                'distinct',
            ],
            'data.*.priority' => [
                'integer',
                'distinct',
            ],
        ];
    }
}
