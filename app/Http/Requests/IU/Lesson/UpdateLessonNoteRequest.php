<?php

namespace App\Http\Requests\IU\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonNoteRequest extends FormRequest
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
            'text' => [
                'string',
                'nullable',
                'max:65535',
            ],
        ];
    }
}
