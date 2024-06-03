<?php

namespace App\Http\Requests\AF\Adverts;

use App\DataObject\AdvertData;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfAdvertCreateRequest extends FormRequest
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
            'name'          => [
                'required',
                'string',
                'min:3',
                'max:20',
            ],
            'url'           => [
                'required',
                'url',
                'min:10',
                'max:2048',
            ],
            'img'           => [
                'required',
                'mimes:jpg,jpeg,png,gif',
                'max_mb:2',
            ],
            'expires_at'    => [
                'required',
                'date',
                'after_or_equal:' . Carbon::now()->format('Y-m-d'),
            ],
            'status'        => ['required', Rule::in(AdvertData::getStatuses())]
        ];
    }

    public function attributes()
    {
        return [
            'img' => 'image',
        ];
    }
}
