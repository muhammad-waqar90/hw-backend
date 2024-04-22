@extends('emails.layouts.emailTemplate')

@section('title')
  <title>Purchase Refund</title>
@endsection

@section('content')
  <table width="100%" style="font-size:15px;font-family: 'Montserrat';">
    <tbody>
    <tr>
      <td style="width: 100%;color: #222222;font-weight:500;">
        Purchased items successfully refunded from HijazWorld!
      </td>
    </tr>
    <tr>
      <td style="color: #222222;font-weight:500;">
        You can check details of Refunded Items below:
      </td>
    </tr>
    <tr>
      <td height="5" style="height: 5px;line-height: 5px;"></td>
    </tr>
    <tr>
      <td height="15" style="height: 15px;line-height: 15px;"></td>
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

            @foreach ($items as $item)
            @if($loop->odd)
            <tr>
            @else
            <tr style="background-color:#F2F4FB;">
            @endif
              <td>
                £{{ number_format((float)$item->amount, 2, '.', '') }}
              </td>
              <td>
                {{ $item->entity_name }}
              </td>
              <td>
                {{ __($item->entity_type == 'exam_accesses' ? 'exam' : ($item->entity_type == 'ebook' ? 'lecture notes' : $item->entity_type)) }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td height="10" style="height: 10px;line-height: 10px;"></td>
    </tr>
    <tr>
      <td style="width: 100%;color: #222222;font-weight:500;">
        Thank you for using HijazWorld!
      </td>
    </tr>
    </tbody>
  </table>
@endsection
