<?php

namespace App\Http\Requests\AF\Lessons\Ebooks;

use Illuminate\Foundation\Http\FormRequest;

class AfLessonEbookRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'with_src' => [
                'required',
                'boolean',
            ],
        ];
    }
}
