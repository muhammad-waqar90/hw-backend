@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Unclaimed</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        Your ticket regarding <span style="color: #1982EF">"{{ $ticketSubject }}"</span> has been unclaimed by <span style="font-weight:700;">{{ $adminName }}</span>
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
      This is to let you know that the above ticket has been returned to an unclaimed status and needs to be reallocated.
      </td>
    </tr>
    <tr>
        <td height="40" style="height: 40px;line-height: 40px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection
