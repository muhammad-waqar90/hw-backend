@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Claimed</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        Your ticket regarding <span style="color: #1982EF;">"{{ $ticketSubject }}"</span> has been claimed by <span style="font-weight:600;">{{ $adminName }}</span>
      </td>
    </tr>
    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        We wanted to let you know that our colleague is working on your query and will send you a response shortly.
      </td>
    </tr>
    <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection
