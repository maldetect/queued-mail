<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;
use Log;

class MailController extends Controller
{
    public function send(Request $request)
    {

        $now = now();

        $mail = ['body' => 'test body at ' . $now];

        SendEmail::dispatch($mail)->onQueue('email');;

        Log::info('Dispatched mail ' . $now);
        return 'Dispatched mail ' . $now;
    }
}
