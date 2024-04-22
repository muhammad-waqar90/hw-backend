<?php

namespace App\Http\Requests\AF;

use App\DataObject\CouponData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfCouponUpdateRequest extends FormRequest
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
            'name'                  => 'required|string|min:4|max:50',
            'description'           => 'present|nullable|string|min:4|max:250',
            'status'                => ['required', 'integer', Rule::in(CouponData::getStatuses())],
            'redeem_limit'          => 'required|integer|min:1|max:16777215',
            'redeem_limit_per_user' => 'required|integer|min:1|max:16777215',
        ];
    }
}
