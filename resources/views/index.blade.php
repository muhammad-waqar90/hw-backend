<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">

        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
        <link rel="icon" href="/favicon/favicon.ico" type="image/x-icon">

        <title>{{isset($title) && $title ? $title : 'Hijaz World'}}</title>
        <meta name="description" content="{{isset($description) && $description ? $description :  'Learning platform'}}">

        <link rel="stylesheet" href="{{ mix('css/app.css') }}">

        <script src="https://js.stripe.com/v3"></script>
    </head>
    <body>
        <div id="app" class="app">
            <app></app>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
