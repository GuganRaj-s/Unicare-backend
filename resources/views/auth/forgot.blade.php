@extends('layouts.login')
@section('content')
<div id="appCapsule">
    <div class="section mt-5 mb-5 p-2" id="SendOtpRequestSection">
        <form method="POST"  id="forgot_form" class="forgot-form">
            @csrf
            <div class="card" style="width: 330px; margin: 1% auto;">
                <div class="card-body pb-1">
                    <div class="section mt-2 text-center mb-5">
                        <h1 style="font-size: 25px;">Forgot Password</h1>
                    </div>

                    <div class="form-group basic">
                        <div class="input-wrapper">
                            <label class="label" for="mobile">Mobile Number</label>
                            <input type="tel" class="form-control" name="mobile" id="mobile"  placeholder="Enter Your Registered Mobile Number" required autofocus>
                        </div>
                    </div>

                    <div class="form-group basic text-center mt-3">
                        <button type="submit" class="btn btn-outline-success me-1 mb-1"> <ion-icon class="icon" name="lock-open-outline"></ion-icon> Send OTP</button>

                        <div><a href="{{url('login')}}" class="text-muted"> Back To Login</a></div>
                    </div>
                    <div id="show_error" class="alert alert-outline-danger mb-1" role="alert" style="display:none;"></div>
                </div>
            </div>
        </form>
    </div>
    <div class="d-none" id="OTPSEction">
        <div class="section mt-5 text-center">
            <h1>Reset Password</h1>
            <h5>Enter 4 digit verification code. Wen sent to your mobile</h5>
            <h4><span id="mobile_number_label"></span><span id="back_to_forgot" title="Change Number" class="pointer"><ion-icon name="pencil-outline" title="Change Number"></ion-icon> </span> </h4>
        </div>
        <div class="section mb-5 p-2">
            <form method="POST"  id="otp_form" class="otp-form">
                @csrf
                <input type="hidden"  name="mobile_number" id="mobile_number" value="">
                <input type="hidden"  name="security_token"  id="security_token" value="">
                <div class="card" style="width: 330px; margin: 1% auto;">
                    <div class="card-body pb-1">
                        <div class="form-group basic">
                            <label class="label text-center" for="smscode" style="font-size:25px;">OTP</label>
                            <input type="text" class="form-control verification-input" id="smscode"  name="smscode" placeholder="••••"
                                maxlength="4">
                        </div>

                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label" for="password">Password</label>
                                <input type="password" class="form-control" name="password" id="password"  placeholder="Enter New Password" maxlength="16" required>
                            </div>
                        </div>

                        <div class="form-group basic">
                            <div class="input-wrapper">
                                <label class="label" for="confirm_password">Confirm Password</label>
                                <input type="text" class="form-control" name="confirm_password" id="confirm_password"  placeholder="Enter Confirm Password" maxlength="16" required>
                            </div>
                        </div>
                        <div class="form-group basic text-center mt-3">
                            <button type="submit" class="btn btn-outline-success me-1 mb-1"> <ion-icon class="icon" name="lock-open-outline"></ion-icon> Verify & Reset Password</button>
                            <div><a href="{{url('login')}}" class="text-muted">Back To Login</a></div>
                        </div>
                        <div id="show_error2" class="alert alert-outline-danger mb-1" role="alert" style="display:none;"></div>
                        <div id="show_error3" class="alert alert-outline-success mb-1" role="alert" style="display:none;"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

