@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Not Claimed</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>Ticket #{{ $ticketId }} -
        <span style="color: #1982EF">"{{ $ticketSubject }}"</span>
        has not been claimed.
      </td>
    </tr>

    <tr>
      <td>
        None of the admins has claimed the ticket in over 48h.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    @include('emails.AF.Ticket.afTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection
