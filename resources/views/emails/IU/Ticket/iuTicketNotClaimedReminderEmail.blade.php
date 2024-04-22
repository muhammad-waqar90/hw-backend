@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Not Claimed</title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">Ticket #{{ $ticketId }} -
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span>
        has not been claimed.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;">
        None of the admins has claimed the ticket in over 48h.
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


