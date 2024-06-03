@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Claimed But Not Responded By Admin</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>Ticket #{{ $ticketId }} -
        <span style="color: #1982EF">"{{ $ticketSubject }}"</span>
        has claimed but not responded
      </td>
    </tr>

    <tr>
      <td>
        Admin claimed the ticket 24h ago and not responded yet.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    @include('emails.AF.Ticket.afTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection


