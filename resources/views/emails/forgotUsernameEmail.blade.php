@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Forgot Username</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860">
  <tbody>
    <tr>
      <td>
        Please find your username (Login ID) below
      </td>
    </tr>
    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td>
        <table style="background:#E9F2FD;border-radius: 4px;">
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


