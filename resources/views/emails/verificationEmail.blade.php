@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Verification email</title>
@endsection

@section('content')
<table style="font-size:15px;font-family: 'Montserrat';">
  <tbody>
    <tr>
      <td style="color: #222222;font-family: 'Montserrat';">
        Thanks for choosing Hijaz World! So we can finish setting you up, please confirm your email address using the link below.
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
                <a style="color: #2c86ee;text-decoration:none;font-family: 'Montserrat';" href="{{config('app.url')}}/?verification_token={{$token}}">
                  <strong style="font-weight:normal;word-break:break-all;">{{config('app.url')}}/?verification_token={{$token}}</strong>
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
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    <tr>
      <td style="color: #222222;font-family: 'Montserrat';">
        Please note your login-id (username)
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
      <td style="color: #222222;font-family: 'Montserrat';">
        If you didn’t sign up to Hijaz World, you can ignore this email.
      </td>
    </tr>
  </tbody>
</table>
@endsection
