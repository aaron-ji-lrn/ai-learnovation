import * as React from 'react';

const GridCol = ({ label, value }: { label: string; value: string }) => {
    return (
        <div className="md:grid md:grid-cols-2 hover:bg-gray-50 md:space-y-1 space-y-1 p-2 border-b">
            <p className="text-gray-600 text-sm">{label}</p>
            <p>{value}</p>
        </div>
    );
};

type Response = {
    response: string;
    changed_response: boolean;
    attempted: number;
};

type AssessmentProps = {
    data: {
        question_id: string;
        question_type: string;
        question_text: string;
        difficulty: string;
        tags: string[];
        time_duration: string;
        time_spent: string;
        response: Response[];
    };
};

export default function ({ data }: AssessmentProps) {
    const [isOpen, setIsOpen] = React.useState(false);

    const responses = Array.isArray(data.response)
        ? data.response
        : [data.response || []];
    const questionData = JSON.parse(data.question_text);
    let value = questionData.stimulus;
    if (questionData.type === 'orderlist') {
        value = `Stimulus: ${questionData.stimulus}, List: ${questionData.list.join(', ')}`;
    }
    
    return (
        <div className="pl-2">
            <div
                onClick={() => {
                    setIsOpen(!isOpen);
                }}
                className="flex cursor-pointer  justify-between text-left p-3 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <span>
                    <span className="text-sm">Question ID</span>{' '}
                    <span className="ml-2 font-bold">{data.question_id}</span>
                </span>
                <i className="fa-solid fa-plus text-sm"></i>
            </div>
            {isOpen && (
                <div className="ml-2">
                    <GridCol label="Question type" value={data.question_type} />
                    <GridCol
                        label="Question Stimulus"
                        value={value}
                    />

                    <GridCol label="Difficulty" value={data.difficulty} />
                    <GridCol label="Tags" value={data.tags.toString()} />
                    <GridCol label="Time duration" value={data.time_duration} />
                    <GridCol label="Time spent" value={data.time_spent} />
                    <GridCol label="Responses" value="" />

                    <div className="pl-4">
                        {responses.map((response, index) => (
                            <div key={index}>
                                <GridCol
                                    label="Response"
                                    value={["association", "orderlist","clozeassociation","plaintext", "mcq", "clozedropdown"].includes(questionData.type) ? JSON.parse(response.response).value : response.response}
                                />
                                <GridCol
                                    label="No. of attempts"
                                    value={
                                        response?.attempted.toString() || '0'
                                    }
                                />
                                <GridCol
                                    label="Response changed"
                                    value={
                                        response.changed_response.toString() ||
                                        ''
                                    }
                                />
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
