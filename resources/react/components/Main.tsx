import Dropdown from './Dropdown';
import Spinner from './Spinner';
import Card from './Card';
import { useState } from 'react';
import { aiFeedback } from '../utils/axios';
// import axios from 'axios';

export interface Student {
    name: string;
    id: string;
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
    const students = JSON.parse(
        document.getElementById('app').getAttribute('data-students'),
    ).map((student: any) => ({
        name: student.name,
        id: student.student_id,
        assessments: student.assessments,
        graded: false,
    }));

    const studentItems = students.map((student) => ({
        name: student.name,
        id: student.id,
        assessments: student.assessments,
        graded: true,
    }));

    // @ts-ignore
    const studentFeedback = async (data) => {
        // @ts-ignore
        const result = await aiFeedback(data);
        console.log(result);
    };

    return (
        <div className="min-h-screen flex flex-col">
            <header className="bg-blue-500 text-gray-700 font-bold text-2xl p-4">
                Holistic Feedback
            </header>
            <div className="flex p-4 flex-row">
                <div className="flex p-4 text-xl">Generate feedback by</div>
                <Dropdown
                    items={studentItems}
                    label="Student"
                    onSelect={(student) => {
                        setSelectedStudent(student);
                        studentFeedback(student);
                    }}
                    placeholder="Search a student"
                />
            </div>
            <div className="flex-1 p-4">
                <div className="flex space-x-4">
                    <div className="flex-1 bg-gray-100 p-4">
                        {selectedStudent && (
                            <Card title={selectedStudent.name}>
                                <div>
                                    <GridCol
                                        label="Student ID"
                                        value={selectedStudent.id}
                                    />
                                    <GridCol label="Assessments" value="" />
                                    {selectedStudent.assessments.map(
                                        (assessment, assessKey) => (
                                            <div
                                                key={assessKey}
                                                className="pl-4"
                                            >
                                                <GridCol
                                                    label="Question ID"
                                                    value={
                                                        assessment.question_id
                                                    }
                                                />
                                                <GridCol
                                                    label="Question Response"
                                                    value={
                                                        assessment.question_text
                                                    }
                                                />
                                                <GridCol
                                                    label="difficulty"
                                                    value={
                                                        assessment.difficulty
                                                    }
                                                />
                                                <GridCol
                                                    label="Tags"
                                                    value={assessment.tags.toString()}
                                                />
                                                <div className="md:grid md:grid-cols-2 hover:bg-gray-50 md:space-y-1 space-y-1 p-2 border-b">
                                                    <p className="text-gray-600">
                                                        Responses
                                                    </p>
                                                    <textarea
                                                        rows={8}
                                                        className="block px-0 w-full text-sm text-gray-800 bg-white border-0 dark:bg-gray-800 focus:ring-0 dark:text-white dark:placeholder-gray-400"
                                                        placeholder="Responses"
                                                        value={JSON.stringify(
                                                            assessment.responses,
                                                        )}
                                                    />
                                                </div>
                                            </div>
                                        ),
                                    )}
                                </div>
                            </Card>
                        )}
                    </div>
                    <div className="flex-1 bg-gray-200 p-4">
                        <div className="mt-2">
                            <Spinner>Loading</Spinner>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Main;
