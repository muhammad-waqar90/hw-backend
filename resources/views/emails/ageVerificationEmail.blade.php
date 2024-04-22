@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Verification email</title>
@endsection

@section('content')
<table style="font-size:15px;font-family: 'Montserrat';">
  <tbody>
    <tr>
      <td style="color: #222222;font-family: 'Montserrat';">
        Your child <b style="color: #666666;">{{$user->first_name}} {{$user->last_name}}</b> ({{$childProfile->email}}) has registered to use the Hijaz World educational platform. As they are under the age of 13 years we require parent/guardian consent for them to join the platform.
        <br><br>
        You have been provided as the parent/guardian. If you consent for them to use the platform, please click the link below. No further action is required.
        <br><br>
        <strong>PLEASE NOTE:</strong> During examinations pictures/videos are taken of the candidate to prevent cheating. By clicking the link below you approve of these images being taken and stored.
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
                <a style="color: #2c86ee;text-decoration:none;font-family: 'Montserrat';" href="{{config('app.url')}}/?age_verification_token={{$token}}">
                    <strong style="font-weight:normal;word-break:break-all;">{{config('app.url')}}/?age_verification_token={{$token}}</strong>
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
        If you do not wish for your child to use the Hijaz World platform, please ignore this email.
      </td>
    </tr>
  </tbody>
</table>
@endsection
