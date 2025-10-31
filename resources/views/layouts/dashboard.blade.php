<!doctype html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
    <meta name="theme-color" content="#000000">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.apname') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('website/img/favicon.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?V={{ config('app.version')}}">
</head>
<style>
#btn-back-to-top {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 5px;
    height: 38px;
    border-radius: 25px;
    width: 40px;
    display: none;
    z-index:9999;
}
</style>
<body>
    <!-- loader -->
    <div id="loader">
        <div class="spinner-grow text-success" role="status"></div>
    </div>
    <!-- * loader -->

    <button type="button" class="btn btn-danger btn-floating btn-lg" id="btn-back-to-top"><ion-icon name="arrow-up-outline"></ion-icon></button>

    <!-- App Header Start -->
    <div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="#" class="headerButton" data-bs-toggle="modal" data-bs-target="#sidebarPanel">
                <ion-icon name="menu-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">
            <p>{{ config('app.apname') }}</p>
        </div>
        <div class="right">
            <a href="{{ url('dashboard/profile')}}" class="headerButton">
                <ion-icon class="icon" name="person-circle-outline"></ion-icon><span class="d-none d-md-inline"> {{ ucfirst(Auth::user()->name) }}</span>
            </a>
        </div>
    </div>
    <!-- * App Header End -->

    @yield('content')

    @include('layouts.partial.nav')

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
    <script src="{{ asset('js/moment.min.js') }}?V={{ config('app.version')}} "></script>  
    <script src="{{ asset('js/vue/vue.min.js') }}?V={{ config('app.version')}} "></script>
    <script src="{{ asset('js/vue/axios.min.js') }}?V={{ config('app.version')}}"></script>
    <script src="{{ asset('js/vue/home.js') }}?V={{ config('app.version')}}"></script>
</body>
<script>
    //Get the button
let mybutton = document.getElementById("btn-back-to-top");

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function () {
  scrollFunction();
};

function scrollFunction() {
  if (
    document.body.scrollTop > 20 ||
    document.documentElement.scrollTop > 20
  ) {
    mybutton.style.display = "block";
  } else {
    mybutton.style.display = "none";
  }
}
// When the user clicks on the button, scroll to the top of the document
mybutton.addEventListener("click", backToTop);

function backToTop() {
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
}
</script>
</html>
