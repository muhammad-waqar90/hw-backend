@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Account Deleted</title>
@endsection

@section('content')
<table style="line-height: 21px;font-size:14px;font-weight: 400;font-family: 'Montserrat';color:#384860;">
  <tbody>
    <tr>
      <td>
        Your account deletion request has processed.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        All of your saved data, including your account information, saved settings, and any saved files or documents,
        have been permanently removed from our system. This action cannot be undone.
      </td>
    </tr>

    <tr>
      <td height="20" style="height: 20px;line-height: 20px;"></td>
    </tr>
    <tr>
      <td>
        Thank you for using HijazWorld!
      </td>
    </tr>
  </tbody>
</table>
@endsection
