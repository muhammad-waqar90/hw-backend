@extends('emails.layouts.emailTemplate')

@section('title')
  <title>Purchase Confirmation</title>
@endsection

@section('content')
  <table width="100%" style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
    <tbody>
      <tr>
        <td>
          You have successfully purchased items from HijazWorld!
        </td>
      </tr>
      <tr>
        <td height="10" style="height: 10px;line-height: 10px;"></td>
      </tr>
      <tr>
        <td>
          You can check purchase details below:
        </td>
      </tr>
      <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
      </tr>
      <tr>
        <td>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;background-color:#F1F5F9;border-radius:5px; table-layout: fixed;">
            <tbody>

              <tr style="font-weight:700;font-size:16px;line-height: 24px;">
                <td style="padding:10px 10px 0px 20px; width: 60px;">Amount</td>
                <td style="padding:10px 10px 0px 10px;">No. of items</td>
                <td style="padding:10px 20px 0px 10px; width: 130px;">Date</td>
              </tr>

              <tr>
                <td style="padding: 10px 10px 10px 20px; width: 60px;">
                  {{ $purchaseHistory->currency_symbol }}{{ number_format((float)$purchaseHistory->amount, 2, '.', '')}}
                </td>
                <td style="padding:10px 10px 10px 10px;">
                  {{ $purchaseHistory->purchase_items_count }}
                </td>
                <td style="padding:10px 20px 10px 10px;">
                  {{ $purchaseHistory->created_at }}
                </td>
              </tr>

            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
      </tr>
      <tr>
        <td style="font-weight:700;font-size:20px;line-height: 24px;">
          Items Purchased
        </td>
      </tr>
      <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
      </tr>
      <tr>
        <td>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;background-color:#F1F5F9;border-radius:5px; table-layout: fixed;">
            <tbody>

              <tr style="font-weight:700;font-size:16px;line-height: 24px;">
                <td style="padding:10px 10px 0px 20px; width: 60px;">Amount</td>
                <td style="padding:10px 10px 0px 10px;">Name</td>
                <td style="padding:10px 20px 0px 10px; width: 100px;">Type</td>
              </tr>

              @foreach ($purchaseHistory->purchaseItems as $item)
                @if($item->entity_type != 'shipping')
                  <tr>
                    <td style="padding:10px 10px 0px 20px;">
                      {{ $purchaseHistory->currency_symbol }}{{ number_format((float)$item->amount, 2, '.', '') }}
                    </td>
                    <td style="padding:10px 10px 0px 10px;">
                      {{ $item->entity_name }}
                    </td>
                    <td style="padding:10px 20px 0px 10px;">
                      {{ __($item->entity_type == 'exam_accesses' ? 'exam' : ($item->entity_type == 'ebook' ? 'lecture notes' : $item->entity_type)) }}
                    </td>
                  </tr>
                @endif
              @endforeach

              <tr><td colspan="3" style="padding-top:10px;"></td></tr>
            </tbody>
          </table>
        </td>
      </tr>
      <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
      </tr>
      {{-- Shipping Cost --}}
      @foreach ($purchaseHistory->purchaseItems as $item)
      @if($item->entity_type == 'shipping')
        <tr>
          <td style="font-weight:700;font-size:20px;line-height: 24px;">
            Shipping Cost
          </td>
        </tr>
        <tr>
          <td height="20" style="height: 20px;line-height: 20px;"></td>
        </tr>
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;background-color:#F1F5F9;border-radius:5px; table-layout: fixed;">
              <tbody>

                <tr style="font-weight:700;font-size:16px;line-height: 24px;">
                  <td style="padding: 10px 10px 0px 20px; width: 60px;">Amount</td>
                  <td style="padding:10px 10px 0px 10px;">Name</td>
                  <td style="padding:10px 20px 0px 10px; width: 100px;">Type</td>
                </tr>

                <tr>
                  <td style="padding: 10px 10px 10px 20px;">
                    {{ $purchaseHistory->currency_symbol }}{{ number_format((float)$item->amount, 2, '.', '') }}
                  </td>
                  <td style="padding:10px 10px 10px 10px;">
                    {{ $item->entity_name }}
                  </td>
                  <td style="padding:10px 20px 10px 10px;">
                    {{ __($item->entity_type == 'exam_accesses' ? 'exam' : ($item->entity_type == 'ebook' ? 'lecture notes' : $item->entity_type)) }}
                  </td>
                </tr>

              </tbody>
            </table>
          </td>
        </tr>
      @endif
      @endforeach
      {{-- Shipping Cost End --}}
      <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
      </tr>
      <tr>
        <td> Thank you for using HijazWorld! </td>
      </tr>
    </tbody>
  </table>
@endsection
