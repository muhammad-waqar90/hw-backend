@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket claimed but not responded by admin</title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">Ticket #{{ $ticketId }} -
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span>
        has claimed but not responded
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;">
        Admin claimed the ticket 24h ago and not responded yet.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    @include('emails.AF.Ticket.afTicketLink', ['ticketId' => $ticketId])
    <tr>
        <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
  </tbody>
</table>
@endsection


