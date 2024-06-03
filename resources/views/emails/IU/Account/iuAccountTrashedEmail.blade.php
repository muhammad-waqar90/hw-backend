@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Account Trashed</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        Your account deletion request has been submitted to our team.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        We are sorry to hear that you have decided to delete your account on HijazWorld.
        We value your privacy and want to ensure that your account is deleted completely and securely.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        Please note that your account can be restored within the next <b>30 days</b>. If you want to restore your account
        click the link below and login by using your credentials.
      </td>
    </tr>

    <tr>
      <td height="40" style="height: 40px;line-height: 40px;"></td>
    </tr>
    <tr>
      <td>
          <table style="background:#1982EF;border-radius: 4px;">
              <tbody>
              <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
              <tr>
                  <td width="6" style="width:6px;"></td>
                  <td>
                      <a style="color: #FFFFFF;text-decoration:none;font-weight:600" href="{{config('app.url')}}/login">
                          <strong style="word-break:break-all;">{{config('app.url')}}/login</strong>
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
        Thank you for using HijazWorld!
      </td>
    </tr>
  </tbody>
</table>
@endsection
