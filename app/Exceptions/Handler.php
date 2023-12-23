<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // $this->renderable(function (\Exception $e) {
        //     if ($e->getPrevious() instanceof \Illuminate\Session\TokenMismatchException) {
        //         return redirect()->route('login');
        //     };
        // });

        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect()->route('login');
        }

        return parent::render($request, $exception);
        
    }

    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'status' => 0,
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], 200); //parent method return 422
    }

    //Customized error message for token mis-match by reghu
    protected function unauthenticated($request, AuthenticationException $exception) 
    {
        if ($request->expectsJson()) {
            Log::debug("Unauthorized User - Invalid Token :: ".$request);
            return response()->json(['status' => 2, 'message' => 'Unauthorized User - Invalid Token'], 401);
        }

        return redirect()->route('login');
    }


}
