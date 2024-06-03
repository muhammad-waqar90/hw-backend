<?php

namespace App\Http\Requests\IU;

use Illuminate\Foundation\Http\FormRequest;

class IuFeedbackRequest extends FormRequest
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
            'feedback'   => [
                'required',
                'string',
                'min:1',
                'max:10000',
            ],
        ];
    }
}
