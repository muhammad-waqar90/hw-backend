@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Username</title>
@endsection

@section('content')
<table style="font-size:15px;font-family: 'Montserrat';">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">
        Please find your username (Login ID) below
      </td>
    </tr>
    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td>
        <table style="background:#f2f4fb;border-radius: 20px;">
          <tbody>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
            <tr>
              <td width="6" style="width:6px;"></td>
              <td>
                {{ $username }}
              </td>
              <td width="6" style="width:6px;"></td>
            </tr>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>
@endsection


