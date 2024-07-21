import * as React from "react";

interface CardProps {
    title: string;
}

const Card: React.FC<React.PropsWithChildren<CardProps>> = ({ title, children }) => {
    return (
        <div className="bg-white p-4 rounded-md">
            <h2 className="text-gray-600 text-lg font-semibold pb-4">{title}</h2>
            <div className="container">
                {children}
            </div>
        </div>
    );
};

export default Card;
