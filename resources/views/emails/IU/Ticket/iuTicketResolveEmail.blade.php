@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Resolved</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>Ticket closed
        <span style="color: #1982EF">"{{ $ticketSubject }}"</span>
        – please note that your ticket query has been responded to and the ticket is now closed.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    <tr>
      <td>
        Admin: <b><span style="font-weight:600;font-style: italic;">{{ $adminName }}</span></b>
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
                <span style="color: #1982EF">"</span>
                <span>{{ $adminMessage }}</span>
                <span style="color: #1982EF">"</span>
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
        If this does not solve your issue, please login to Hijaz World and reopen the ticket.
      </td>
    </tr>
    <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection


