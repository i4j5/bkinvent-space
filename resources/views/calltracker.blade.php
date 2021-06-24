<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Call Tracker</title>
        <script src="{{ mix('js/visit-tracker.js') }}" defer></script>
    </head>
    <body class="antialiased">
        <div class="{{ env('CALL_TRACKER_CSS_CLASS') }}">
            qqq
        </div>
        <a class="{{ env('CALL_TRACKER_CSS_CLASS') }}">qqq</a>
    </body>
</html>
