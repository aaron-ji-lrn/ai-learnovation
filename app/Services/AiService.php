<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.openai.com/v1/']);
    }

    public function getFeedback(array $assessmentData)
    {
        $response = $this->client->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "
                        You are a teacher reviewing the class assessments. 
                        Based on all the student's reponses to each level of questions,
                        Provide holistic feedback for the class, then identify in terms of potential improvement areas for the whole class",
                    ],
                    [
                        "role" => "user",
                        "content" => json_encode($assessmentData),
                    ],
                
                ],
                "temperature" => 0.7,
                "max_tokens" => 300,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getStudentFeedback(array $assessmentData)
    {
        $response = $this->client->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "
                        You are a teacher reviewing this student assessment. 
                        Based on the question's difficulty and the student's responses,
                        Provide holistic feedback for the student, then identify in terms of potential improvement areas for the student",
                    ],
                    [
                        "role" => "user",
                        "content" => json_encode($assessmentData),
                    ],
                
                ],
                "temperature" => 0.7,
                "max_tokens" => 300,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getSprintSummary($tickets)
    {
        $response = $this->client->post('chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system', 
                        'content' => 
                            'You are an AI representative for Yoda team to give a summary about the team last sprint work,' . 
                            'You can introduce yourself so other teams know who you are,' .
                            'You need to tell what this team has delivered in the last sprint, and what is in progress,' . 
                            'You need to mention what code repository the ticket was working on and is it a task or feature, bug fix or back patch,' .
                            'You need to provide basic information about the ticket, mainly from the summary,' . 
                            'You can use description of the ticket to help you summarize the ticket if you need to,' .
                            'You need to keep the mescillaneous at the end of delievered or in progress part,' .
                            'You will need provide the summary in 3 minutes time in total.',

                ],
                    ['role' => 'user', 'content' => "all the last sprint work as tickets: $tickets"],
                ],
            ]
        ]);

        $responseBody = json_decode($response->getBody(), true);
        return $responseBody['choices'][0]['message']['content'];
    }

    public function generateAudio($content, $folder)
    {
        set_time_limit(300); // 300 seconds = 5 minutes
        $data = [
            'model' => 'tts-1',
            'input' => $content,
            'voice' => 'alloy',
        ];
        $format = '.mp3';
        $filename = $folder.'/sprint_speech_' .date('Y-m-d') . $format;
        
        try {
            // Send the POST request
            $response = $this->client->post('audio/speech', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                    'Content-Type'  => 'application/json',
                ],
                'json' => $data,
                'sink' => $filename, // Save the response directly to a file
            ]);
        
            $data = [
                'success' => true,
                'status' => $response->getStatusCode(),
                'audio' => $filename,
            ];
            
        } catch (RequestException $e) {
            $data = [
                'success' => false,
                'error' => 'Failed to generate audio, '. $e->getMessage(),
                'details' => $e->getResponse()->getBody(),
            ];
        }

        return $data;
    }
}
