<?php

namespace App\Http\Requests\AF\Modules;

use Illuminate\Foundation\Http\FormRequest;

class AfCourseModuleUpdateRequest extends FormRequest
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
                'min:5',
                'max:60',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
            'img' => [
                'nullable',
                'mimes:jpg,jpeg',
                'max_mb:10',
            ],
            'video_preview' => [
                'nullable',
                'string',
                'max:250',
            ],
            'order_id' => [
                'required',
                'integer',
            ],
            'ebook_price' => [
                'required',
                'gte:0',
                'lte:5000',
            ],
            'module_has_exam' => [
                'required',
                'boolean',
            ],
            'book_id' => [
                'nullable',
                'numeric',
                'exists:products,id',
            ],
        ];
    }
}
