<?php

namespace App\Http\Requests\AF\Courses;

use Illuminate\Foundation\Http\FormRequest;

class AfCourseDiscountStatusUpdateRequest extends FormRequest
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
            'course_id'     => [
                'required',
                'numeric',
                'exists:courses,id',
            ],
            'is_discounted' => [
                'required',
                'boolean',
            ],
        ];
    }
}
