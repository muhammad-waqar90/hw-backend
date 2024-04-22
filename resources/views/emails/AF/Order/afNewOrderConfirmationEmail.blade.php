@extends('emails.layouts.physicalProductEmailTemplate')

@section('title')
  <title>New Order</title>
@endsection

@section('content')
  <table width="100%" style="font-size:15px;font-family: 'Montserrat';">
    <tbody>
    <tr>
      <td style="width: 100%;color: #222222;font-weight:500;">
        Physical item(s) has been successfully purchased from HijazWorld!
      </td>
    </tr>
    <tr>
      <td style="color: #222222;font-weight:500;">
        You can check purchase details below:
      </td>
    </tr>
    <tr>
      <td height="5" style="height: 5px;line-height: 5px;"></td>
    </tr>
    {{-- <tr>
      <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;background-color:#F2F4FB;text-align:center;">
          <tbody>
            <tr style="font-weight:600;">
              <td>
                Amount
              </td>
              <td>
                No. of items
              </td>
              <td>
                Date
              </td>
            </tr>
            <tr>
              <td width="30%">
                {{ $purchaseHistory->currency_symbol }}{{ number_format((float)$purchaseHistory->amount, 2, '.', '') }}
              </td>
              <td width="30%">
                {{ $purchaseHistory->purchaseItems->count() }}
              </td>
              <td width="40%">
                {{ $purchaseHistory->created_at }}
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr> --}}
    <tr>
      <td height="15" style="height: 15px;line-height: 15px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';font-weight:600;">
        Items purchased:
      </td>
    </tr>
    <tr>
      <td height="5" style="height: 5px;line-height: 5px;"></td>
    </tr>
    <tr>
      <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;text-align:center;">
          <tbody>
            <tr style="font-weight:600;background-color:#F2F4FB;">
              <td>
                Amount
              </td>
              <td>
                Name
              </td>
              <td>
                Type
              </td>
            </tr>


            @foreach ($purchaseHistory->purchaseItems as $item)
            @if($item->entity_type === 'physical_product')
            @if($loop->odd)
            <tr>
            @else
            <tr style="background-color:#F2F4FB;">
            @endif
              <td>
                {{ $purchaseHistory->currency_symbol }}{{ number_format((float)$item->amount, 2, '.', '') }}
              </td>
              <td>
                {{ $item->entity_name }}
              </td>

              <td>
                {{ $item->entity_type }}
              </td>
            </tr>
            @endif
            @endforeach
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
        <td style="font-family: 'Montserrat';font-weight:600;">
          Shipping Address:
        </td>
      </tr>
      <tr>
        <td>
            {{ $purchaseHistory->shippingDetails[0]->address }} {{ $purchaseHistory->shippingDetails[0]->city }} {{ $purchaseHistory->shippingDetails[0]->country }} {{ $purchaseHistory->shippingDetails[0]->postal_code }}
        </td>
      </tr>
    <tr>
      <td height="10" style="height: 10px;line-height: 10px;"></td>
    </tr>
    </tbody>
  </table>
@endsection
