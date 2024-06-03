<?php

namespace App\Http\Requests\AF\Lessons;

use Illuminate\Foundation\Http\FormRequest;

class AfLessonSortingRequest extends FormRequest
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
            '*.id' => [
                'required',
                'integer',
            ],
            '*.order_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
