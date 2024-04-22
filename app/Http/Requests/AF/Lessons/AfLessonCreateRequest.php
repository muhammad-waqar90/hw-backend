<?php

namespace App\Http\Requests\AF\Lessons;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AfLessonCreateRequest extends FormRequest
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
            'name' => 'required|string|min:5|max:50',
            'description' => 'required|string|min:10|max:5000',
            'img' => 'nullable|mimes:jpg,jpeg|max_mb:10',
            'video' => 'required|string|max:250',
            'order_id' => 'required|integer',
            'published' => 'required|boolean',
            'publish_at' => 'sometimes|after_or_equal:' . Carbon::now()->format('Y-m-d')
        ];
    }
}
