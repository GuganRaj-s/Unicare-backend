@extends('layouts.page')
@section('content')
<div id="appCapsule">
    <profile-comp></profile-comp>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
    <toast-msg></toast-msg>
</div>
@endsection
