@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Submitted</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>Your ticket regarding 
        <span style="color: #1982EF;">"{{ $ticketSubject }}"</span> 
          has been submitted to our team.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    <tr>
      <td style="font-style: italic;">
        The message that we received from you was:
      </td>
    </tr>

    <tr>
      <td>
        <table style="background:#E9F2FD;border-radius: 4px;">
          <tbody>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
            <tr>
              <td width="6" style="width:6px;"></td>
              <td>
                <span style="color: #1982EF;">"</span>
                <span>{{ $userMessage }}</span>
                <span style="color: #1982EF;">"</span>
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
      Thank you for raising a ticket. We will respond very soon and no later than 48 hours.
      </td>
    </tr>
  </tbody>
</table>
@endsection


