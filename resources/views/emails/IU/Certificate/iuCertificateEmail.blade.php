@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Certificate of Completion </title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">
        You successfully completed <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $entityType }}"</span>
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
                  <span style="color: #333333;">
                    Congratulations on successfully completing "{{ $entityType }}"
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
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';">
        The certificate can be downloaded using the link below:
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
                <a style="color: #2c86ee;text-decoration:none;font-family: 'Montserrat';" href="{{config('app.url')}}/iu/profile/certificates">
                  <strong style="font-weight:normal;word-break:break-all;">{{config('app.url')}}/iu/profile/certificates</strong>
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
