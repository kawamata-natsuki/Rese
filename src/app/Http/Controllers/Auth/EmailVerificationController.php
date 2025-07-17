<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return view('auth.email-verification');
    }

    public function verify()
    {
        //
    }

    public function resend()
    {
        //
    }

    public function check()
    {
        //
    }
}
