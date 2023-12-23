<?php

namespace App\Http\Controllers;

use App\SecurityToken;
use App\SmsGateway;
use App\ViewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\SmsGatewayController;


class SecurityTokenController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SecurityToken  $securityToken
     * @return \Illuminate\Http\Response
     */
    public function show(SecurityToken $securityToken)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SecurityToken  $securityToken
     * @return \Illuminate\Http\Response
     */
    public function edit(SecurityToken $securityToken)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SecurityToken  $securityToken
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SecurityToken $securityToken)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SecurityToken  $securityToken
     * @return \Illuminate\Http\Response
     */
    public function destroy(SecurityToken $securityToken)
    {
        //
    }
}
