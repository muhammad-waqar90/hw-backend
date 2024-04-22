@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Delete Account</title>
@endsection

@section('content')
<table style="font-size:15px">
  <tbody>
    <tr>
      <td style="font-size:15px;font-family: 'Montserrat';">
        Your account deletion request has processed.
      </td>
    </tr>

    <tr>
      <td height="25" style="height: 25px;line-height: 25px;"></td>
    </tr>
    <tr>
      <td style="font-family: 'Montserrat';">
        All of your saved data, including your account information, saved settings, and any saved files or documents,
        have been permanently removed from our system. This action cannot be undone.
      </td>
    </tr>

    <tr>
      <td height="10" style="height: 10px;line-height: 10px;"></td>
    </tr>
    <tr>
      <td style="width: 100%;color: #222222;font-weight:500;">
        Thank you for using HijazWorld!
      </td>
    </tr>
  </tbody>
</table>
@endsection
