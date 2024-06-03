@extends('emails.layouts.emailTemplate')

@section('title')
  <title>Purchase Refund</title>
@endsection

@section('content')
  <table width="100%" style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
    <tbody>
    <tr>
      <td>
        Purchased items successfully refunded from HijazWorld!
      </td>
    </tr>
    <tr>
      <td height="10" style="height: 10px;line-height: 10px;"></td>
    </tr>
    <tr>
      <td>
        You can check details of Refunded Items below:
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;background-color:#F1F5F9;border-radius:5px;  table-layout: fixed;">
          <tbody>
            <tr style="font-weight:700;font-size:16px;line-height:24px;">
              <td style="padding:10px 10px 0px 20px; width: 60px;">
                Amount
              </td>
              <td style="padding:10px 10px 0px 10px;">
                Name
              </td>
              <td style="padding:10px 20px 0px 10px; width: 100px;">
                Type
              </td>
            </tr>

            @foreach ($items as $item)
              <tr>
                <td style="padding:10px 10px 0px 20px;">
                  £{{ number_format((float)$item->amount, 2, '.', '') }}
                </td>
                <td style="padding:10px 10px 0px 10px;">
                  {{ $item->entity_name }}
                </td>
                <td style="padding:10px 20px 0px 10px;">
                  {{ __($item->entity_type == 'exam_accesses' ? 'exam' : ($item->entity_type == 'ebook' ? 'lecture notes' : $item->entity_type)) }}
                </td>
              </tr>
            @endforeach

            <tr><td colspan="3" style="padding-top:10px;"></td></tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        Thank you for using HijazWorld!
      </td>
    </tr>
    </tbody>
  </table>
@endsection
