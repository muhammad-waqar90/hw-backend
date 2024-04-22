@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Attention Required</title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">We require your attention regarding
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span> ticket
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;">
        Admin has responded to your ticket and your action is needed to complete your ticket request.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])

    <tr>
        <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    <tr>
      <td style="font-family: 'Montserrat';">
        Please note, you have 24h to respond to this ticket or we will assume that your ticket query has been solved.
      </td>
    </tr>
    <tr>
        <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
  </tbody>
</table>
@endsection


