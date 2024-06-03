@extends('emails.layouts.emailTemplate')

@section('title')
    <title>GDPR - Export Personal Data</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        Thank you for your patience.
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        Please click the link below to download your personal data.
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
                <a style="color: #FFFFFF;text-decoration:none;font-weight:600" href="{{config('app.url')}}/api/gdpr/user/{{$uuid}}">
                  <strong style="word-break:break-all;">{{config('app.url')}}/api/gdpr/user/{{$uuid}}</strong>
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
        This link will remain valid for 7 days.
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        Please don't hesitate to get in touch if you have any additional queries.
      </td>
    </tr>
  </tbody>
</table>
@endsection
