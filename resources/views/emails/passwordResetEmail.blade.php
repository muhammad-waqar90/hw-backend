@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Password reset</title>
@endsection

@section('content')
<table style="font-size:15px;font-family: 'Montserrat';">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">
        Click the link below to reset your password for your Hijaz World account.
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
                <a style="color: #2c86ee;text-decoration:none;font-size:15px;font-family: 'Montserrat';" href="{{config('app.url')}}/reset-password/{{$token}}">
                    <strong style="font-weight:normal;word-break:break-all;">{{config('app.url')}}/reset-password/{{$token}}</strong>
                </a>
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


