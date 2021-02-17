<?php

namespace App\Http\Controllers\Mail;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\MyMail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Log;

class MailController extends Controller
{

    public function send(Request $request)
    {


        $mails = $request->all();

        $validator = Validator::make($mails, [
            'mail' => 'required|array',
            'mail.*.subject' => 'required|string',
            'mail.*.body' => 'required|string',
            'mail.*.attachments' => 'nullable|array',
            'mail.*.attachments.*.base64' => 'required_with:mail.*.attachments|base64',
            'mail.*.attachments.*.filename' => 'required_with:mail.*.attachments',
            'mail.*.email_address' => 'required|email',
            'api_token' => 'required'
        ]);

        if ($validator->fails()) {
            $response = [
                'errors' => $validator->messages(),
                'success' => 'false',

            ];
            return response()->json($response, 422);
        }

        if (!$this->verifyToken($mails['api_token'])) {
            $response = [
                'errors' => 'Unauthorized',
                'success' => 'false'

            ];
            return response()->json($response, 401);
        }
        foreach ($mails['mail'] as $key => $mail) {
            SendEmail::dispatch($mail)->onQueue('email');;

            Log::info('Dispatched emails ' . $key);
        }


        return response()->json(['success' => 'true', 'message' => 'Dispatched emails']);
    }

    private function verifyToken($token)
    {
        if ($token == "token") {
            return true;
        }

        return false;
    }

    public function list()
    {
        $keys = Redis::eval("return redis.call('keys','*')", 0);

        $pending = [];
        $completed = [];

        foreach ($keys as $key) {
            try {
                $fields = Redis::eval("return redis.call('HGETALL','" . $key . "')", 0);
                if (in_array('pending', $fields)) {
                    array_push($pending, $fields);
                } else if (in_array('completed', $fields)) {
                    array_push($completed, $fields);
                }
            } catch (\Exception $e) {
            }
        }

        return response()->json([
            'completed' => $completed,
            'pending' => $pending
        ]);
    }
}
