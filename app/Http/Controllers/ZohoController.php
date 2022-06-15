<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class ZohoController extends Controller
{
    public function deal(Request $request) {

        $client_id = "1000.E55902GEKDDH4P9VNOG80VX5DDGDAQ";
        $client_secret = "01f62fbe9ffc16ecce73c6a3cb434f069e4101c1bd";
        $code = "1000.adac43756ba5bc29adc8d4d1fafa64dd.fafc4c670c32dbf36779bb66e627081c";
        // https://api-console.zoho.com/client/1000.E55902GEKDDH4P9VNOG80VX5DDGDAQ
        // ZohoCRM.modules.ALL

        $refresh_token = "1000.29a1fec11b37dd5427fc0e057ea871c6.fd170ca73fd3e78b5db1a21f30372701";
        $task = 'New test task ' . time();
        $client = new Client();

        /*
        // Get refresh_token
        $response = $client->request('POST', 'https://accounts.zoho.com/oauth/v2/token',
        [
            'form_params' => [
                'grant_type' => "authorization_code",
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'code' => $code,
            ]
        ]);

        $body = json_decode((string)$response->getBody());

        if (empty($body->access_token)) {
            print_r($body);die();
        }
        */

        // Get access_token
        $response = $client->request('POST', "https://accounts.zoho.com/oauth/v2/token?refresh_token=$refresh_token&grant_type=refresh_token&client_id=$client_id&client_secret=$client_secret&code=$code", []);
        $body = json_decode((string)$response->getBody());
        $token = isset($body->access_token)?$body->access_token:'';

        if ($token) {

            // Create Leads
            $data = [
                "data" => [
                    [
                        "Company" => "Test Company Name",
                        "Last_Name" => "Smala",
                        "City" => "Dnepr"
                    ],
                ]
            ];

            $fields = json_encode($data);

            // run to add leads
            $response = $client->request('POST', 'https://www.zohoapis.com/crm/v2/Leads', ['headers' => [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($fields),
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ],
                'body' => $fields,
            ]);

            $bodyLead = json_decode((string)$response->getBody());

            echo '<pre>';
            print_r($bodyLead);
            echo '</pre>';
            // @end Create Leads


            $dealId = isset($bodyLead->data[0]->details->id)?$bodyLead->data[0]->details->id:'';
            var_dump($dealId);

            // Create Tasks
            $data = [
                "data" =>
                    [
                        [
                            "Task Owner" => 'To Smala',
                            '$se_module' => "Leads",
                            "What_Id" =>  $dealId,
                            "Subject" => $task,
                        ]
                    ]
            ];

            $fields = json_encode($data);

            // run to add task
            $response = $client->request('POST', 'https://www.zohoapis.com/crm/v2/Tasks',['headers'=>
                [
                    'Content-Type' => 'application/json',
                    'Content-Length' => strlen($fields),
                    'Authorization'=> 'Zoho-oauthtoken '.$token
                ],
                'body' => $fields,
            ]);

            $bodyTask = json_decode((string)$response->getBody());

            echo '<pre>';
            print_r($bodyTask);
            echo '</pre>';

            // @end Create Tasks
        }
    }
}