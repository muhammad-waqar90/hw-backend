<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    @yield ('title')
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body>
  <table role="presentation" width="100%" height="100%" style="border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;font-family: 'Montserrat';letter-spacing:0.2px;" border="0" cellspacing="0" cellpadding="0">
    <tbody>
      <tr height="32" style="height:32px"><td></td></tr>

      <tr align="center">
        <td>
          <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:0;border-spacing:0;padding-bottom:20px;max-width: 544px;">
            <tbody>
              <tr>
                <td style="width:640px">
                  @include('emails.partials.header')
                </td>
              </tr>
              <tr height="30" style="height:30px"><td></td></tr>
              <tr>
                <td style="color: #121A26;font-size: 24px;font-weight: 700;padding:0;font-family: 'Montserrat';">Dear {{ $userProfile ? "$userProfile->first_name $userProfile->last_name": 'User' }},</td>
              </tr>
              <tr height="24" style="height:24px"><td></td></tr>
              <tr>
                <td>
                  @yield('content')
                </td>
              </tr>
              <tr height="30" style="height:30px"><td></td></tr>
              <tr>
                <td>
                  @include('emails.partials.signature')
                </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>

      <tr height="32" style="height:32px"><td></td></tr>
    </tbody>
  </table>
</body>
</html>