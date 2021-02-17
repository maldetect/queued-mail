<?php

namespace Tests\Unit;

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
    public function test_email_successful_queued()
    {
        Mail::fake();

        $response = $this->post('/api/send?api_token=token', $this->mail);
        $response->assertStatus(200);

        Mail::assertQueued(MyMail::class);
    }

    public function test_post_more_than_one_email_queued()
    {
        Mail::fake();
        $mail1 = $this->mail;
        $mail2 = $this->mail;

        $mail1['mail'][0]['subject']="mail1";
        $mail2['mail'][0]['subject']="mail2";
        array_push($mail1['mail'],$mail2['mail'][0]);
        $response = $this->post('/api/send?api_token=token', $mail1);
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

    public function test_email_without_attachments_queued()
    {
        Mail::fake();
        $mail = $this->mail;
        unset($mail['mail'][0]['attachments']);

        $response = $this->post('/api/send?api_token=token', $mail);
        $response->assertStatus(200);

        Mail::assertQueued(MyMail::class);
    }

    public function test_filename_is_required_if_has_attachments()
    {
        $mail = $this->mail;
        unset($mail['mail'][0]['attachments'][0]['filename']);
        $response = $this->post('/api/send?api_token=token', $mail);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mail.0.attachments.0.filename']);
    }

    public function test_base64_is_required_if_has_attachments()
    {
        $mail = $this->mail;
        unset($mail['mail'][0]['attachments'][0]['base64']);
        $response = $this->post('/api/send?api_token=token', $mail);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mail.0.attachments.0.base64']);
    }





    public function test_base64_validation()
    {
        $mail = $this->mail;
        $mail['mail'][0]['attachments'][0]['base64'] = "#%8sd%76";
        $response = $this->post('/api/send?api_token=token', $mail);

        $response->assertStatus(422);
        $response->assertExactJson(['errors' =>
            ['mail.0.attachments.0.base64' => [0 => 'validation.base64'],

    ],'success'=>'false']);
    }

    public function test_list_jobs()
    {

        $response = $this->get('/api/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'completed' ,  'pending'
        ]);
    }


}
