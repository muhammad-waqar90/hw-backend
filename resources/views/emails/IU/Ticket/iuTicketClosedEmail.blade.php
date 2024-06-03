@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Closed</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>Ticket
        <span style="color: #1982EF">"{{ $ticketSubject }}"</span> closed.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        please note that your ticket has been closed.
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
        <td height="40" style="height: 40px;line-height: 40px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection


