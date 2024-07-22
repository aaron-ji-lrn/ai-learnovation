import StudentDropdown from './StudentDropdown';
import Spinner from './Spinner';
import Card from './Card';
import { useState } from 'react';
import { aiFeedback } from '../utils/axios';
import QuestionContainer from './QuestionContainer';
import MarkdownContainer from './MarkdownContainer';
import StudentAvatar from './StudentAvatar';
import AiAvatar from './AiAvatar';
import Dropdown from './Dropdown';
import ClassDropdown from './ClassDropdown';
// import axios from 'axios';

export interface Student {
    name: string;
    id: string;
    sessionId: string;
    age: number;
    nationality: string;
    assessments: any[];
    graded: boolean;
}

const GridCol = ({ label, value }: { label: string; value: string }) => {
    return (
        <div className="md:grid md:grid-cols-2 hover:bg-gray-50 md:space-y-1 space-y-1 p-2 border-b">
            <p className="text-gray-600 text-sm">{label}</p>
            <p>{value}</p>
        </div>
    );
};

function Main() {
    const [selectedStudent, setSelectedStudent] = useState<Student>(null);
    const [selectedClass, setSelectedClass] = useState(null);
    const [activity, setActivity] = useState('');
    const [feedbackLoading, setFeedbackLoading] = useState(false);
    const [aiFeedbackResponse, setAiFeedbackResponse] = useState(null);
    const [category, setCategory] = useState('');

    const students = JSON.parse(
        document.getElementById('app').getAttribute('data-students'),
    ).map((student: any) => ({
        name: student.name,
        id: student.student_id,
        sessionId: student.session_id,
        age: student.age,
        nationality: student.nationality,
        assessments: student.assessments,
        graded: false,
    }));

    // @ts-ignore
    const studentFeedback = async (data: any) => {
        // @ts-ignore
        setFeedbackLoading(true);
        const result = await aiFeedback(activity, data.sessionId);
        setAiFeedbackResponse(result);
        setFeedbackLoading(false);
    };

    const onSelectStudent = (student) => {
        setAiFeedbackResponse('');
        setSelectedStudent(student);
        studentFeedback(student);
    };

    const onSelectClass = (selection) => {
        // TODO: do the class feedback
    };

    const classes = [];

    const showContent = selectedStudent || selectedClass;

    return (
        <div className="lrn-hf min-h-screen flex flex-col">
            <header className="text-white font-bold text-2xl p-4">
                Learnovation: AI Holistic Feedback
            </header>
            <div className="flex p-4 flex-row">
                <div className="flex p-4 text-xl">Generate feedback for</div>
                <Dropdown
                    label="Select an activity"
                    items={[
                        {
                            name: 'ai_english_assessment',
                        },
                    ]}
                    onSelect={(selection) => {
                        setActivity(selection?.name);
                    }}
                />
                {activity && (
                    <Dropdown
                        label="Select a category"
                        items={[
                            {
                                name: 'Class',
                            },
                            {
                                name: 'Student',
                            },
                        ]}
                        onSelect={(selection) => {
                            setCategory(selection?.name);
                        }}
                    />
                )}
                {category === 'Class' && (
                    <ClassDropdown
                        items={classes}
                        label="Select a class"
                        onSelect={onSelectClass}
                        placeholder="Search a class"
                    />
                )}
                {category === 'Student' && (
                    <StudentDropdown
                        items={students}
                        label="Select a atudent"
                        onSelect={onSelectStudent}
                        placeholder="Search a student"
                    />
                )}
            </div>
            {showContent && (
                <div className="flex-1 p-4">
                    <div className="flex space-x-4">
                        <div className="flex-1 bg-gray-100 p-4">
                            {selectedStudent && (
                                <Card
                                    title={selectedStudent.name}
                                    avatar={<StudentAvatar />}
                                >
                                    <div>
                                        <GridCol
                                            label="Student ID"
                                            value={selectedStudent.id}
                                        />
                                        <GridCol label="Assessments" value="" />
                                        {selectedStudent.assessments.map(
                                            (assessment, assessKey) => (
                                                <div key={assessKey}>
                                                    <QuestionContainer
                                                        data={assessment}
                                                    />
                                                </div>
                                            ),
                                        )}
                                    </div>
                                </Card>
                            )}
                        </div>
                        <div className="flex-1 bg-gray-200 p-4">
                            <div className="mt-2">
                                {feedbackLoading && (
                                    <div className="flex justify-center mt-12">
                                        <Spinner>Generating...</Spinner>
                                    </div>
                                )}
                                {aiFeedbackResponse && (
                                    <Card
                                        title="AI Generated Feedback"
                                        avatar={<AiAvatar />}
                                    >
                                        <MarkdownContainer>
                                            {aiFeedbackResponse}
                                        </MarkdownContainer>
                                    </Card>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

export default Main;
