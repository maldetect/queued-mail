<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Validator;
use Log;

class MailController extends Controller
{
    /**
     * Expect json
     * {
     * "mail": [
     *   {
     *       "subject": "subject1",
     *       "body": "body1",
     *       "attachments": [
     *           {
     *              "base64": "iVBORw"
     *               "filename": "filename.png"
     *           }
     *       ],
     *       "email_address": "mail@teste.com"
     *   }
     * ]
     *}
     */
    public function send(Request $request)
    {


        $mails = $request->all();

        $validator = Validator::make($mails, [
            'mail'=>'required|array',
            'mail.*.subject' => 'required|string',
            'mail.*.body' => 'required|string',
            'mail.*.attachments' => 'nullable|array',
            'mail.*.attachments.*.base64' => 'required_with:mail.*.attachments',
            'mail.*.attachments.*.filename' => 'required_with:mail.*.attachments',
            'mail.*.email_address' =>'required|email',
            'api_token'=>'required'
        ]);

        if ($validator->fails())
        {
            $response = [
                'errors' => $validator->messages(),
                'success' => 'false',

            ];
            return response()->json($response, 422);
        }

        if (!$this->verifyToken($mails['api_token'])){
            $response = [
                'errors' => 'Unauthorized',
                'success' => 'false'

            ];
            return response()->json($response, 401);
        }
        foreach ($mails['mail'] as $key => $mail){
            SendEmail::dispatch($mail)->onQueue('email');;

            Log::info('Dispatched mail ' . $key);
        }


        return 'Dispatched mails ';
    }

    private function verifyToken($token){
        if ($token=="token"){
            return true;
        }

        return false;
    }
}
