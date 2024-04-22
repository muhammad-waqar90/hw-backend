@extends('emails.layouts.emailTemplate')

@section('title')
    <title>Test Email</title>
@endsection

@section('content')
    <div>hello {{ $name }}</div>
@endsection
