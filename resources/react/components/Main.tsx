import Dropdown from './Dropdown';
import Spinner from './Spinner';
import Card from './Card';
import { useState } from 'react';
import { aiFeedback } from '../utils/axios';
import QuestionContainer from './QuestionContainer';
import MarkdownContainer from './MarkdownContainer';
import StudentAvatar from './StudentAvatar';
import AiAvatar from './AiAvatar';
// import axios from 'axios';

export interface Student {
    name: string;
    id: string;
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
    const [feedbackLoading, setFeedbackLoading] = useState(false);
    const [aiFeedbackResponse, setAiFeedbackResponse] = useState(null);

    const students = JSON.parse(
        document.getElementById('app').getAttribute('data-students'),
    ).map((student: any) => ({
        name: student.name,
        id: student.student_id,
        age: student.age,
        nationality: student.nationality,
        assessments: student.assessments,
        graded: false,
    }));

    // @ts-ignore
    const studentFeedback = async (data: any) => {
        // @ts-ignore
        setFeedbackLoading(true);
        const result = await aiFeedback(data);
        setAiFeedbackResponse(result);
        setFeedbackLoading(false);
    };

    const onSelectStudent = (student) => {
        setAiFeedbackResponse('');
        setSelectedStudent(student);
        studentFeedback(student);
    };

    return (
        <div className="lrn-hf min-h-screen flex flex-col">
            <header className="text-white font-bold text-2xl p-4">
                Learnovation: AI Holistic Feedback
            </header>
            <div className="flex p-4 flex-row">
                <div className="flex p-4 text-xl">Generate feedback by</div>
                <Dropdown
                    items={students}
                    label="Student"
                    onSelect={onSelectStudent}
                    placeholder="Search a student"
                />
            </div>
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
                                    <GridCol
                                        label="Age"
                                        value={selectedStudent.age.toString()}
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
        </div>
    );
}

export default Main;
