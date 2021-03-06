<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    
        <title>Jumia Task</title>
        <base href="{{ url('/') }}">
    
        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet" >
    </head>
    <body>
        <div id="app"></div>    
        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}"></script>
        @stack('custom-scripts')
        
    </body>
</html>
