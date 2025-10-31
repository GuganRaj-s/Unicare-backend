<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $message = "Hello from the controller!";
        return view('home');
    }

    public function CryptoEncryption($input) {
        $saltCiphertext = base64_decode($input);

        $salt = substr($saltCiphertext, 8, 8);
        $ciphertext = substr($saltCiphertext, 16);


        // Separate key and IV
        $keyIv = $this->EVP_BytesToKey($salt, env('ENC_SALT_KEY'));
        $key = substr($keyIv, 0, 32);
        $iv = substr($keyIv, 32, 16);

        // Decrypt using key and IV
        $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        // while ($msg = openssl_error_string())
        // echo $msg . "<br />\n";

        return json_decode($decrypted, true);

    }

    public function EVP_BytesToKey($salt, $password) {
	    $bytes = ''; 
	    $last = '';
	    while(strlen($bytes) < 48) {
	        $last = hash('md5', $last . $password . $salt, true);
	        $bytes.= $last;
	    }
	    return $bytes;
	}	

    

    
}
