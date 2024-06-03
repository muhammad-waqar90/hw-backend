<?php

namespace App\Http\Requests\AF\Courses;

use Illuminate\Foundation\Http\FormRequest;

class AfCourseUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:4',
                'max:60',
            ],
            'category_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'price' => [
                'required',
                'gte:0',
                'lte:5000',
            ],
            'tier_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'img' => [
                'nullable',
                'mimes:jpg,jpeg',
                'max_mb:10',
            ],
            'video_preview' => [
                'nullable',
                'string',
            ],
        ];
    }
}
