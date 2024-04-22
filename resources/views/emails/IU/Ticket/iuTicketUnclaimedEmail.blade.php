@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Unclaimed</title>
@endsection

@section('content')
<table style="font-size:15px;">
  <tbody>
    <tr>
      <td style="font-family: 'Montserrat';">
        Your ticket regarding <span style="color: #2c86ee;font-family: 'Montserrat';">"{{ $ticketSubject }}"</span> has been unclaimed by <b style="color: #333333;">{{ $adminName }}</b>
      </td>
    </tr>
    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';">
      This is to let you know that the above ticket has been returned to an unclaimed status and needs to be reallocated
      </td>
    </tr>
    <tr>
        <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection
