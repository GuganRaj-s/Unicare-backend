<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="theme-color" content="#000000">
    <title>{{ config('app.apname') }}</title>
    <!--link rel="icon" type="image/png" href="{{ asset('website/img/favicon.png') }}" sizes="32x32"-->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?V={{ config('app.version')}}">
</head>

<body>


    <!-- App Header Start -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
        </div>
        <div class="pageTitle">
            <p>{{ config('app.apname') }}</p>
        </div>
        <div class="right">
        </div>
    </div>
    <!-- * App Header End -->


    @yield('content')

    <div class="appBottomMenu">
        <div class="appFooter">
            <div class="footer-title">
                Copyright © {{ config('app.apname') }}  {{date('Y')}}. All Rights Reserved.
            </div>
        </div>
    </div>

    <!-- Footer -->
    <script src="{{ asset('js/jquery-3.3.1.min.js') }}?V={{ config('app.version')}}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}?V={{ config('app.version')}}"></script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="{{ asset('js/jquery.cookie.min.js') }}?V={{ config('app.version')}}"></script>
    <script src="{{ asset('js/crypto-js.js') }}?V={{ config('app.version')}}"></script>
</body>
</html>
