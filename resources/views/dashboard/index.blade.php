@extends('layouts.page')
@section('content')
<div id="appCapsule">
    <div class="section wallet-card-section pt-1">
    <div class="section mt-4 mb-4 p-0">
        <wallet-comp ref="refWalletComp"></wallet-comp>
        <toast-msg-top></toast-msg-top>
    </div>
</div>

@endsection
