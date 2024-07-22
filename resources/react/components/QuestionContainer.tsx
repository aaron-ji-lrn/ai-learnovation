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
    changed: boolean;
    timestamp: string;
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
        responses: Response[];
    };
};

export default function ({ data }: AssessmentProps) {
    const [isOpen, setIsOpen] = React.useState(false);

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
                        value={data.question_text}
                    />

                    <GridCol label="difficulty" value={data.difficulty} />
                    <GridCol label="Tags" value={data.tags.toString()} />
                    <GridCol label="Time duration" value={data.time_duration} />
                    <GridCol label="Time spent" value={data.time_spent} />
                    <GridCol label="Responses" value="" />

                    <div className="pl-4">
                        {data.responses.map((response, index) => (
                            <div key={index}>
                                <GridCol
                                    label="Response"
                                    value={response.response}
                                />
                                <GridCol
                                    label="Times started"
                                    value={response.timestamp}
                                />
                                <GridCol
                                    label="Response changed"
                                    value={response.changed.toString()}
                                />
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
