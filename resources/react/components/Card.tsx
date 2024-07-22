import * as React from 'react';

interface CardProps {
    title: string;
    avatar?: React.ReactNode;
}

const Card: React.FC<React.PropsWithChildren<CardProps>> = ({
    title,
    children,
    avatar = null,
}) => {
    return (
        <div className="bg-white p-4 rounded-md">
            <div className="flex items-center">
                {avatar}
                <h2 className="text-gray-600 text-lg pl-2 pt-4 font-semibold pb-4">
                    {title}
                </h2>
            </div>
            <div className="container mt-2">{children}</div>
        </div>
    );
};

export default Card;
