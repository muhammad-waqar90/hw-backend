@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Admin Account Created</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        <span style="font-weight:700;color: #121A26;font-size:24px;line-height: 29px;">Your administrator account has been created!</span>
      </td>
    </tr>
    <tr>
        <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
        <td>
            Please note your login ID (username)
        </td>
    </tr>
    <tr>
        <td>
            <table style="background:#E9F2FD;border-radius: 4px;">
                <tbody>
                    <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
                    <tr>
                        <td width="6" style="width:6px;"></td>
                    <td>
                        {{$userName}}
                    </td>
                    <td width="6" style="width:6px;"></td>
                    </tr>
                    <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
      <td height="40" style="height: 40px;line-height: 40px;"></td>
    </tr>
    <tr>
      <td>
        Please click the link below to set your password.
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        <table style="background:#1982EF;border-radius: 4px;">
          <tbody>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
            <tr>
              <td width="6" style="width:6px;"></td>
              <td>
                <a style="color: #FFFFFF;text-decoration:none;font-weight:600;" href="{{config('app.url')}}/reset-password/{{$token}}">
                    <strong style="word-break:break-all;">{{config('app.url')}}/reset-password/{{$token}}</strong>
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


