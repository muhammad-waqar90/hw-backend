@extends('emails.layouts.emailTemplate')

@section('title')
    <title>GDPR - Export Personal Data</title>
@endsection

@section('content')
<table style="font-size:15px;font-family: 'Montserrat';">
  <tbody>
    <tr>
      <td style="color: #222222;font-family: 'Montserrat';">
        <p>Thank you for your patience.</p>
        <p></p>
        <p>Please click the link below to download your personal data.</p>
      </td>
    </tr>

    <tr>
      <td>
        <table style="background:#f2f4fb;border-radius: 20px;">
          <tbody>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
            <tr>
              <td width="6" style="width:6px;"></td>
              <td>
                <a style="color: #2c86ee;text-decoration:none;font-family: 'Montserrat';" href="{{config('app.url')}}/api/gdpr/user/{{$uuid}}">
                  <strong style="font-weight:normal;word-break:break-all;">{{config('app.url')}}/api/gdpr/user/{{$uuid}}</strong>
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
      <td style="color: #222222;font-family: 'Montserrat';">
        <p>This link will remain valid for 7 days.</p>
        <p></p>
        <p>Please don't hesitate to get in touch if you have any additional queries.</p>
      </td>
    </tr>
  </tbody>
</table>
@endsection
