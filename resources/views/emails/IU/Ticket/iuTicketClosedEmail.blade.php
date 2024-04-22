@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Closed </title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">Ticket
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span> closed
        <br /> – please note that your ticket has been closed.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
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


