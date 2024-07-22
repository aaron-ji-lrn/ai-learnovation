<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    protected $aiService;
    protected $itemData = [];

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function getQuestionResponseId($user_id, $response_id)
    {
        $consumer_id = '0034';
        return $consumer_id . '_' . $user_id . '_' . $response_id;
    }

    public function getActivityData($activityId=null, $sessionId=null)
    {
        $sql = 'select u.username, t.test_uuid, t.score, t.max_score, t.num_questions, t.count_attempted,
            TIMEDIFF(t.time_completed,t.time_prepared) as session_time_spent,
            t.metadata,
            ts.source_sheet_reference as item_reference,
            ts.count_questions_attempted,
            ts.organisation_id,
            tq.widget_response_id,
            tq.attempted as question_attempted,
            tq.type
        from tbl_tests t
        inner join tbl_exams e on e.id=t.exam_id
        inner join tbl_users u on u.user_id=t.user_id
        inner join tbl_test_sheets ts on t.test_id = ts.test_id
        inner join tbl_test_questions tq on tq.test_id=t.test_id and tq.sheet_reference = ts.source_sheet_reference
        where e.code= ?';
        $bindings = [$activityId];
        
        // get data from dexter
        if (!empty($sessionId)) {
            $sql .= ' and t.test_uuid = ?';
            $bindings = array_merge($bindings, [$sessionId]);
        }
        
        $dexterData = DB::connection('dexter')->select($sql, $bindings);

        return $dexterData;
    }

    public function getItemData($item_reference, $organisation_id)
    {
        if (isset($this->itemData[$item_reference])) {
            return $this->itemData[$item_reference];
        } else {
            $ibkData = DB::connection('ibk')->select('
                select s.difficulty, t.name as tag from sheets s
                inner join sheets_tags st on st.sheet_id=s.id
                inner join tags t on t.id = st.tag_id
                where s.reference=? and s.organisation_id=?;', 
                [$item_reference, $organisation_id]
            );
            $this->itemData[$item_reference] = $ibkData;
            return $ibkData;
        }
    }

    public function getResponseData($userId, $responseId)
    {
        $totalResponseIds[] = $this->getQuestionResponseId($userId, $responseId);
        $placeholders = rtrim(str_repeat('?,', count($totalResponseIds)), ',');
        $parameters = array_merge([$userId], $totalResponseIds);

        $qrData = DB::connection('qr')->select("select question,response from get_question_response_with_latest_question(?, ARRAY[$placeholders])", $parameters);

        return $qrData[0] ?? [];
    }

    public function getTimeSpentOnItem($metadata, $itemReference)
    {
        $metadata = json_decode($metadata);
        $sessionItems = $metadata->items;
        $currentItems = array_filter($sessionItems, function($item) use ($itemReference) {
            return $item->reference === $itemReference;
        });
        $currentItem = array_shift($currentItems);
    
        return $currentItem->time ? $currentItem->time .'s' : 0;
    }

    public function getClassData($activityId)
    {
        $questions = $this->getActivityData($activityId);

        $students = [];
        foreach($questions as $i => $question) {
            $studentId = $question->username;
            $qr = $this->getResponseData($studentId, $question->widget_response_id);
            $ibk = $this->getItemData($question->item_reference, $question->organisation_id);
            $studentInfo = [
                'question_id' => 'Question ' . ($i + 1),
                'question_text' => $qr->question,
                'question_type' => $question->type,
                'difficulty' => $ibk[0]->difficulty,
                'tags' => array_column($ibk, 'tag'),
                'time_spent' => $this->getTimeSpentOnItem($question->metadata, $question->item_reference),
                "time_duration" => "1m",
                'response' => [
                    'response' => $qr->response,
                    'attempted' => $question->question_attempted,
                    'changed_response' => $question->question_attempted > 1 ? 'true' : 'false',
                ],
            ];

            $students[$question->test_uuid]['session_id'] = $question->test_uuid;
            $students[$question->test_uuid]['assessments'][] = $studentInfo;
            $students[$question->test_uuid]['student_id'] = $studentId;
            $students[$question->test_uuid]['name'] = $studentId;
            $students[$question->test_uuid]['age'] = 20;
            $students[$question->test_uuid]['nationality'] = "Australian";
        }

        $total = [];
        foreach($students as $student) {
            $total[] = $student;
        }

        return $total;
    }

    public function getStudentData($activityId, $sessionId)
    {
        $questions = $this->getActivityData($activityId, $sessionId);

        $student = [];
        foreach($questions as $i => $question) {
            $studentId = $question->username;
            $qr = $this->getResponseData($studentId, $question->widget_response_id);
            $ibk = $this->getItemData($question->item_reference, $question->organisation_id);
            $studentInfo = [
                'question_id' => 'Question ' . ($i + 1),
                'question_text' => $qr->question,
                'question_type' => $question->type,
                'difficulty' => $ibk[0]->difficulty,
                'tags' => array_column($ibk, 'tag'),
                'time_spent' => $this->getTimeSpentOnItem($question->metadata, $question->item_reference),
                "time_duration" => "1m",
                'response' => [
                    'response' => $qr->response,
                    'attempted' => $question->question_attempted,
                    'changed_response' => $question->question_attempted > 1 ? 'true' : 'false',
                ],
            ];
            $student['student_id'] = $studentId;
            $student['name'] = $studentId;
            $student['age'] = 20;
            $student['nationality'] = "Australian";
            $student['assessments'][] = $studentInfo;
        }

        return [$student];
    }

    public function generate()
    {
        $activityId='ai_english_assessment';
        $students = $this->getClassData($activityId);

        return view('generate', [
            'students' => json_encode($students)
        ]);
    }

    public function aiFeedback($sessionId)
    {
        $activityId='ai_english_assessment';
        // $sessionId='469cf2b8-f16d-4fe2-835d-375bfb888351';

        $data = $this->getStudentData($activityId, $sessionId);
        $feedback = $this->aiService->getStudentFeedback($data);

        return response()->json($feedback['choices'][0]['message']['content']);
    }
}
