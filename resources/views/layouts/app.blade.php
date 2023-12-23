
<!doctype html>
<html lang="zxx">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> UniCare </title>
    <!-- /Required meta tags -->

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('website/img/favicon.png') }}" type="image/x-icon">
    <!-- /Favicon -->

    <!-- All CSS -->

    <!-- Vendor Css -->
    <link rel="stylesheet" href="{{ asset('website/css/vendors.css') }}?V={{ config('app.version')}}">
    <!-- /Vendor Css -->

    <!-- Plugin Css -->
    <link rel="stylesheet" href="{{ asset('website/css/plugins.css') }}?V={{ config('app.version')}}">
    <!-- Plugin Css -->

    <!-- Icons Css -->
    <link rel="stylesheet" href="{{ asset('website/css/icons.css') }}?V={{ config('app.version')}}">
    <!-- /Icons Css -->

    <!-- Style Css -->
    <link rel="stylesheet" href="{{ asset('website/css/style.css') }}?V={{ config('app.version')}}">
    <!-- /Style Css -->

    <!-- /All CSS -->

</head>
<body>
   
   
    @yield('content')


    <!-- Vendor Js -->
    <script src="{{ asset('website/js/vendors.js') }}?V={{ config('app.version')}}"></script>
    <!-- /Vendor js -->

    <!-- Plugins Js -->
    <script src="{{ asset('website/js/plugins.js') }}?V={{ config('app.version')}}"></script>
    <!-- /Plugins Js -->

    <!-- Main JS -->
    <script src="{{ asset('website/js/main.js') }}?V={{ config('app.version')}}"></script>
    <!-- /Main JS -->

    <!-- /JS -->

</body>

</html>
