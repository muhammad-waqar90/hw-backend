@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Certificate of Completion </title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        You successfully completed <span style="color: #1982EF">"{{ $entityType }}"</span>
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    <tr>
      <td>
        <table style="background:#E9F2FD;border-radius: 4px;">
          <tbody>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
            <tr>
              <td width="6" style="width:6px;"></td>
              <td>
                  <span style="color: #000000;">
                    Congratulations on successfully completing <span style="color: #1982EF">"{{ $entityType }}"</span>
                  </span>
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
        The certificate can be downloaded using the link below:
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
                <a style="color: #FFFFFF;text-decoration:none;font-weight: 600;" href="{{config('app.url')}}/iu/profile/certificates">
                  <strong style="word-break:break-all;">{{config('app.url')}}/iu/profile/certificates</strong>
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
