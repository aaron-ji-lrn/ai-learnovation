<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assessment;
use App\Services\AiService;

class AssessmentController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function getStudentData($studentId = null)
    {
        $student1 = [
            "student_id"=> "12345",
            "name" => "John Doe",
            "assessments"=> [
                [
                    "question_id" => "q1",
                    "question_text" => "What is the capital of France?",
                    "difficulty" => "easy",
                    "tags" => ["geography"],
                    "responses"=> [
                        [
                            "response"=> "Paris",
                            "changed"=> false,
                            "timestamp"=> "2024-01-01T12:00:00Z",
                        ]
                    ]
                ],
                [
                    "question_id" => "q2",
                    "question_text" => "Solve the equation: 2x + 3 = 7",
                    "difficulty" => "medium",
                    "tags" => ["math", "algebra"],
                    "responses" => [
                        [
                            "response" => "2",
                            "timestamp" => "2024-01-01T12:02:00Z",
                            "changed" => true,
                        ],
                        [
                            "response" => "x = 2",
                            "timestamp" => "2024-01-01T12:03:30Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q3",
                    "question_text" => "What is the process of photosynthesis?",
                    "difficulty" => "hard",
                    "tags" => ["biology"],
                    "responses" => [
                        [
                            "response" => "Photosynthesis is the process by which plants make their food using sunlight.",
                            "timestamp" => "2024-01-01T12:05:00Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q4",
                    "question_text" => "Name the largest planet in our solar system.",
                    "difficulty" => "easy",
                    "tags" => ["astronomy"],
                    "responses" => [
                        [
                            "response" => "Saturn",
                            "timestamp" => "2024-01-01T12:06:00Z",
                            "changed" => true,
                        ],
                        [
                            "response" => "Jupiter",
                            "timestamp" => "2024-01-01T12:07:15Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q5",
                    "question_text" => "Explain the significance of the Battle of Hastings.",
                    "difficulty" => "medium",
                    "tags" => ["history"],
                    "responses" => [
                        [
                            "response" => "It was a battle that took place in 1066.",
                            "timestamp" => "2024-01-01T12:10:00Z",
                            "changed" => true,
                        ],
                        [
                            "response" => "The Battle of Hastings in 1066 led to the Norman conquest of England.",
                            "timestamp" => "2024-01-01T12:12:00Z",
                            "changed" => false,
                        ],
                    ],
                ]
            ]
        ];
        $student2 = [
            "student_id" => "67890",
            "name" => "Peter Parker",
            "assessments" => [
                [
                    "question_id" => "q1",
                    "question_text" => "What is the capital of Germany?",
                    "difficulty" => "easy",
                    "tags" => ["geography"],
                    "responses" => [
                        [
                            "response" => "Berlin",
                            "timestamp" => "2024-01-02T14:00:00Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q2",
                    "question_text" => "Calculate the value of 5 + 3 * 2",
                    "difficulty" => "medium",
                    "tags" => ["math", "arithmetic"],
                    "responses" => [
                        [
                            "response" => "16",
                            "timestamp" => "2024-01-02T14:02:00Z",
                            "changed" => true,
                        ],
                        [
                            "response" => "11",
                            "timestamp" => "2024-01-02T14:03:30Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q3",
                    "question_text" => "Describe the water cycle.",
                    "difficulty" => "hard",
                    "tags" => ["science"],
                    "responses" => [
                        [
                            "response" => "The water cycle is the process where water evaporates, condenses, and precipitates.",
                            "timestamp" => "2024-01-02T14:05:00Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q4",
                    "question_text" => "Who wrote 'Romeo and Juliet'?",
                    "difficulty" => "easy",
                    "tags" => ["literature"],
                    "responses" => [
                        [
                            "response" => "Charles Dickens",
                            "timestamp" => "2024-01-02T14:06:00Z",
                            "changed" => true,
                        ],
                        [
                            "response" => "William Shakespeare",
                            "timestamp" => "2024-01-02T14:07:15Z",
                            "changed" => false,
                        ],
                    ],
                ],
                [
                    "question_id" => "q5",
                    "question_text" => "What caused the Great Depression?",
                    "difficulty" => "medium",
                    "tags" => ["history"],
                    "responses" => [
                        [
                            "response" => "A series of banking failures.",
                            "timestamp" => "2024-01-02T14:10:00Z",
                            "changed" => true,
                        ],
                        [
                            "response" => "The Great Depression was caused by the stock market crash of 1929 and subsequent banking failures.",
                            "timestamp" => "2024-01-02T14:12:00Z",
                            "changed" => false,
                        ],
                    ],
                ],
            ],
        ];

        $data = [$student1, $student2];

        if ($studentId) {
            $data = array_filter($data, function ($student) use ($studentId) {
                return $student['student_id'] === $studentId;
            });
        }


        return $data;
    }

    public function getFeedback($assessmentId)
    {
        // $assessment = Assessment::findOrFail($assessmentId);

        $data = $this->getStudentData();
        $feedback = $this->aiService->getFeedback($data);

        return view('class_feedback', [
            'feedback' => $feedback['choices'][0]['message']['content'],
            'students' => array_map(function ($student) {
                return $student['student_id'];
            }, $data),
        ]);
    }

    public function generate()
    {
        $students = $this->getStudentData();

        return view('generate', [
            'students' => json_encode($students)
        ]);
    }

    public function getStudentFeedback($sessionId, $studentId)
    {
        // $assessment = Assessment::where('student_id', $studentId)->firstOrFail();
        $data = $this->getStudentData($studentId);
        $feedback = $this->aiService->getStudentFeedback($data);

        return view('student_feedback', [
            'feedback' => $feedback['choices'][0]['message']['content'],
            'students' => [$studentId],
        ]);
    }

    public function aiFeedback(Request $request)
    {
        $data = $request->all();
        $feedback = $this->aiService->getFeedback($data);

        return response()->json($feedback);
    }
}
