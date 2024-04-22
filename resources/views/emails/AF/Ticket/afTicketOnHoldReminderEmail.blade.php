@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket On Hold</title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">Ticket #{{ $ticketId }} -
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span>
        has put on hold but not processed yet.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;">
        Admin put the ticket on hold 5 days ago and not processed yet.
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


