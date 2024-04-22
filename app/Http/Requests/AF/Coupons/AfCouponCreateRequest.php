<?php

namespace App\Http\Requests\AF\Coupons;

use App\DataObject\CouponData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;

class AfCouponCreateRequest extends FormRequest
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
        $maxValue = request()->value_type == CouponData::PERCENTAGE ? 100 : 5000;

        return [
            'name'                  => 'required|string|min:4|max:50',
            'description'           => 'present|nullable|string|min:4|max:250',
            'code'                  => 'required|string|min:6|max:20|unique:coupons,code',
            'value'                 => 'required|gte:1|lte:' . $maxValue,
            'value_type'            => ['required', 'integer', Rule::in(CouponData::getDiscountValueTypes())],
            'status'                => ['required', 'integer', Rule::in(CouponData::getStatuses())],
            'redeem_limit'          => 'required|integer|min:1|max:16777215',
            'redeem_limit_per_user' => 'required|integer|min:1|max:16777215',
            'individual_use'        => 'required|boolean',
            'restrictions'          => 'array|max:100',
            'restrictions.*.id'     => 'required|array',
            'restrictions.*.id.*'   => 'required|integer|min:1',
            'restrictions.*.type'   => ['required', 'string', Rule::in(CouponData::getEntityRestrictionTypes())]
        ];
    }
}
