@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Age Verification</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860">
  <tbody>
    <tr>
      <td>
        Your child <span style="font-weight: 600;color:#2969FF;">{{$user->first_name}} {{$user->last_name}}</span> ({{$childProfile->email}}) has registered to use the Hijaz World educational platform. As they are under the age of 13 years we require parent/guardian consent for them to join the platform.
        <br><br>
        You have been provided as the parent/guardian. If you consent for them to use the platform, please click the link below. No further action is required.
        <br><br>
        <span style="font-weight: 600;">PLEASE NOTE: During examinations pictures/videos are taken of the candidate to prevent cheating. By clicking the link below you approve of these images being taken and stored.</span>
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
                <a style="color:#FFFFFF;text-decoration:none;font-weight:600;" href="{{config('app.url')}}/?age_verification_token={{$token}}">
                    <strong style="word-break:break-all;">{{config('app.url')}}/?age_verification_token={{$token}}</strong>
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
        If you do not wish for your child to use the Hijaz World platform, please ignore this email.
      </td>
    </tr>
  </tbody>
</table>
@endsection
