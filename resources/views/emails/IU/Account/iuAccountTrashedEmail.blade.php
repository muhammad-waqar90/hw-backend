@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Delete Account</title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">
        Your account deletion request has been submitted to our team.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';">
        We are sorry to hear that you have decided to delete your account on HijazWorld.
        We value your privacy and want to ensure that your account is deleted completely and securely.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';">
        Please note that your account can be restored within the next <b>30 days</b>. If you want to restore your account
        click the link below and login by using your credentials.
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
                      <a style="color: #2c86ee;text-decoration:none;font-family: 'Montserrat';" href="{{config('app.url')}}/login">
                          <strong style="font-weight:normal;word-break:break-all;">{{config('app.url')}}/login</strong>
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
      <td style="width: 100%;color: #222222;font-weight:500;">
        Thank you for using HijazWorld!
      </td>
    </tr>
  </tbody>
</table>
@endsection
