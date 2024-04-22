@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Ticket Submitted </title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">Your ticket regarding
        <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"{{ $ticketSubject }}"</span>
          has been updated
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    <tr>
      <td style="font-family: 'Montserrat';font-style: italic;color: #666666;">
        The message that we received from the admin was:
      </td>
    </tr>

    <tr>
      <td>
        <table style="background:#f2f4fb;border-radius: 20px;">
          <tbody>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
            <tr>
              <td width="6" style="width:6px;"></td>
              <td>
                <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"</span>
                  <span style="color: #333333;">{{ $adminMessage }}</span>
                <span style="font-size:15px;font-family: 'Montserrat';color: #2c86ee;">"</span>
              </td>
              <td width="6" style="width:6px;"></td>
            </tr>
            <tr><td colspan="3" style="line-height:4px;height:4px;mso-line-height-rule:exactly;">&nbsp;</td></tr>
          </tbody>
        </table>
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';">
        Please note that we have dealt with your query
      </td>
    </tr>
    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>

    @include('emails.partials.link', ['path' => $iuTicketLink])
  </tbody>
</table>
@endsection


