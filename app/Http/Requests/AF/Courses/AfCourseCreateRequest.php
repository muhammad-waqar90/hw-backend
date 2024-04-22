<?php

namespace App\Http\Requests\AF\Courses;

use Illuminate\Foundation\Http\FormRequest;

class AfCourseCreateRequest extends FormRequest
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
            'name' => 'required|string|min:4|max:50',
            'category_id' => 'required|integer|min:1',
            'description' => 'required|string|min:10|max:5000',
            'price' => 'required|gte:0|lte:5000',
            'tier_id' => 'required|integer|min:1',
            'number_of_levels' => 'required|integer|min:1|max:100',
            'img' => 'required|mimes:jpg,jpeg|max_mb:10',
            'video_preview' => 'nullable|string'
        ];
    }
}
