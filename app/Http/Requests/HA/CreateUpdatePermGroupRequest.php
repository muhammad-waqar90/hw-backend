<?php

namespace App\Http\Requests\HA;

use Illuminate\Foundation\Http\FormRequest;

class CreateUpdatePermGroupRequest extends FormRequest
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
            'name' => 'required|min:2|max:30|unique:perm_groups,name,'. ($this->id ?: ''),
            'users' => 'present|array',
            'permissions' => 'present|array',
            'description' => 'present|string|nullable|max:255'
        ];
    }
}
