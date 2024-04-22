<?php

namespace App\Http\Requests\IU;

use App\DataObject\PaymentGatewaysData;
use App\DataObject\Purchases\PurchaseItemTypeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed items
 * @property mixed paymentMethod
 */
class IuCartCheckoutRequest extends FormRequest
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
            'items' => 'required|array|max:65535',
            'items.*.id' => 'required|integer',
            'items.*.type' => [
                'required',
                'string',
                Rule::in(array_values(PurchaseItemTypeData::getConstants())),
            ],
            // for: [coupons]
            'code' => 'nullable|string|min:6|max:20',
            // for: [stripe]
            'paymentMethod' => 'present|array',
            'paymentMethod.id' => 'string|required_unless:paymentMethod.save,null',
            'paymentMethod.save' => 'boolean|required_unless:paymentMethod.id,null',
            // for: [other than stripe]
            'transactionBy' => ['nullable', 'string', Rule::in(array_values(PaymentGatewaysData::getConstants()))],
            'transactionReceipt' => 'required_unless:transactionBy,null',
            // for: [inapp]
            'transactionReceipt.transactionId' => 'required_if:transactionBy,' . PaymentGatewaysData::INAPP,
            'transactionReceipt.transactionReceipt' => 'required_if:transactionBy,' . PaymentGatewaysData::INAPP,
            'different_shipping_address' => 'required_if:items.*.type,' . PurchaseItemTypeData::PHYSICAL_PRODUCT . '|boolean',
            'shipping_country' => 'exclude_if:different_shipping_address,false|required_with:different_shipping_address|string|min:3|max:100',
            'shipping_city' => 'exclude_if:different_shipping_address,false|required_with:different_shipping_address|string|min:3|max:100',
            'shipping_address' => 'exclude_if:different_shipping_address,false|required_with:different_shipping_address|string|min:3|max:100',
            'shipping_postal_code' => 'exclude_if:different_shipping_address,false|required_with:different_shipping_address|string|min:3|max:20',

        ];
    }
}
