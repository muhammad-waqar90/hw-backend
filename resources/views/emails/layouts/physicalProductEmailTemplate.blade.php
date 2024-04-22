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
<body style="margin:0;padding:0;">
  <table role="presentation" width="100%" height="100%" style="border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;font-family: 'Montserrat';" border="0" cellspacing="0" cellpadding="0">
    <tbody>
      <tr height="32" style="height:32px"><td></td></tr>

      <tr align="center">
        <td>
          <table role="presentation" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;border:0;border-spacing:0;padding-bottom:20px;max-width: 450px;">
            <tbody>
              <tr>
                <td style="color: #2c86ee;font-size: 20px;font-weight: 600;padding:0 10px 20px 10px;font-family: 'Montserrat';">Dear Admin,</td>
              </tr>
              <tr>
                <td style="padding:0 10px 0 10px;">
                  @yield('content')
                </td>
              </tr>
              <tr>
                <td style="padding:0 10px 0 10px;">
                  @include('emails.partials.signature')
                </td>
              </tr>
              <tr>
                <td>
                  @include('emails.partials.footer')
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
