<?php

namespace App\Http\Requests\AF\Users;

use App\DataObject\ActivityStatusData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfUserListRequest extends FormRequest
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
            'searchText'    => [
                'string',
                'nullable',
                'max:100',
            ],
            'activeStatus'  => [
                'nullable',
                'integer',
                Rule::in(array_values(ActivityStatusData::getConstants())),
            ],
            'courseId'      => [
                'nullable',
                'integer',
            ],
        ];
    }
}
