<?php

namespace App\Services;

use GuzzleHttp\Client;

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
}
