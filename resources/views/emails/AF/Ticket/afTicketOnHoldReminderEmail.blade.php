@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket On Hold</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860">
  <tbody>
    <tr>
      <td>Ticket #{{ $ticketId }} -
        <span style="color: #1982EF;">"{{ $ticketSubject }}"</span>
        has put on hold but not processed yet.
      </td>
    </tr>

    <tr>
      <td>
        Admin put the ticket on hold 5 days ago and not processed yet.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    @include('emails.AF.Ticket.afTicketLink', ['ticketId' => $ticketId])
  </tbody>
</table>
@endsection


