@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Attention Required</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>We require your attention regarding
        <span style="color: #1982EF;">"{{ $ticketSubject }}"</span> ticket.
      </td>
    </tr>

    <tr>
      <td>
        Admin has responded to your ticket and your action is needed to complete your ticket request.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    @include('emails.IU.Ticket.iuTicketLink', ['ticketId' => $ticketId])

    <tr>
        <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>

    <tr>
      <td style="font-weight: 600;font-size:15px;">
        Please note, you have 24h to respond to this ticket or we will assume that your ticket query has been solved.
      </td>
    </tr>
  </tbody>
</table>
@endsection


