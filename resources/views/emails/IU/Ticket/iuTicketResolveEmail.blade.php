@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Resolved </title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">Ticket closed
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span>
        – please note that your ticket query has been responded to and the ticket is now closed.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    <tr>
      <td style="font-family: 'Montserrat';">
        Admin: <b><span style="font-family: 'Montserrat';font-style: italic;color: #666666;">{{ $adminName }}</span></b>
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
                <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"</span>
                  <span style="color: #333333;">{{ $adminMessage }}</span>
                <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"</span>
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
      If this does not solve your issue, please login to Hijaz World and reopen the ticket.
      </td>
    </tr>
    <tr>
        <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection


