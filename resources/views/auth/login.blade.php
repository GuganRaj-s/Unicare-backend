@extends('layouts.login')
@section('content')

<div id="appCapsule">
    <div class="section mt-5 mb-5 p-2">
        <form method="POST"  id="login_form" class="request-form ">
                        @csrf
            <div class="card" style="width: 330px; margin: 1% auto;">
                <div class="card-body pb-1">
                    <div class="section mt-2 text-center">
                        <h1>Log in</h1>
                    </div>

                    <div class="form-group basic">
                        <div class="input-wrapper">
                            <label class="label" for="username">Username</label>
                            <input type="text" class="form-control  @error('username') is-invalid @enderror" name="username" id="username" value="{{ old('username') }}" placeholder="Enter Your User ID" required autofocus>
                        </div>
                        <span class="text-danger error-text email_error" style="color: red"></span>
                    </div>

                    <div class="form-group basic">
                        <div class="input-wrapper">
                            <label class="label" for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" required autocomplete="current-password" placeholder="Your password">
                        </div>
                        <span class="text-danger error-text password_error" style="color: red"></span>
                    </div>

                    <div class="form-group basic text-center">
                        <button type="submit" class="btn btn-outline-success me-1 mb-1"> <ion-icon class="icon" name="lock-open-outline"></ion-icon> Login</button>
                        <div><a href="{{url('forget')}}" class="text-muted">Forgot Password?</a></div>
                    </div>
                    <div id="show_error" class="alert alert-outline-danger mb-1" role="alert" style="display:none;"></div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

