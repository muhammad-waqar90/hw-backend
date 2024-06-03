@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Email Verification</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860">
  <tbody>
    <tr>
      <td>
        Thanks for choosing Hijaz World! So we can finish setting you up, please confirm your email address using the link below.
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
                <a style="color:#FFFFFF;text-decoration:none;font-weight:600;" href="{{config('app.url')}}/?verification_token={{$token}}">
                  <strong style="word-break:break-all;">{{config('app.url')}}/?verification_token={{$token}}</strong>
                </a>
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
        Please note your login-id (username)
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
                {{ $userProfile->name }}
              </td>
              <td width="6" style="width:6px;"></td>
            </tr>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    <tr>
      <td>
        If you didn’t sign up to Hijaz World, you can ignore this email.
      </td>
    </tr>
  </tbody>
</table>
@endsection
