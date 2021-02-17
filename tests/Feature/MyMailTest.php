<?php

namespace Tests\Feature;

use App\Mail\MyMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mail;

class MyMailTest extends TestCase
{
    private $mail;

    function __construct()
    {
        parent::__construct();
        $this->mail = [
            'mail' => [
                0 => [
                    'subject' => 'subject from test',
                    'email_address' => 'test@test.com',
                    'body' => 'body test',
                    'attachments' => [
                        0 => [
                            'base64' => base64_encode('dfsgdfFgsdfgbxcvTRT'),
                            'filename' => 'filename.jpg'

                        ]
                    ]
                ]
            ]
        ];;
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_email_success_queued()
    {
        Mail::fake();

        $response = $this->post('/api/send?api_token=token', $this->mail);
        $response->assertStatus(200);

        Mail::assertQueued(MyMail::class);
    }

    public function test_subject_is_required()
    {
        $mail = $this->mail;
        unset($mail['mail'][0]['subject']);
        $response = $this->post('/api/send?api_token=token', $mail);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mail.0.subject']);
    }

    public function test_email_address_is_required()
    {
        $mail = $this->mail;
        unset($mail['mail'][0]['email_address']);
        $response = $this->post('/api/send?api_token=token', $mail);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mail.0.email_address']);
    }
}
