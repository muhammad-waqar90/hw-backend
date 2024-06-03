<?php

namespace App\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class IuQuizSubmitRequest extends FormRequest
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
            'answers' => [
                'required',
                'array',
                'max:65535',
            ],
            'answers.*.answerId' => [
                'present',
                'string_or_array',
            ],
        ];
    }
}
