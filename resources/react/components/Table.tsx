import * as React from 'react';

type StudentItem = {
    avatar: string;
    name: string;
    score: number;
    onClick: () => void;
};

interface StudentTableProps {
    items: StudentItem[];
    onRowSelect: (item: StudentItem) => void;
}

export const StudentTable: React.FC<
    React.PropsWithChildren<StudentTableProps>
> = ({ onRowSelect, items, children }) => {
    return (
        <div className="f-tbl-container">
            <table className="w-full table-auto">
                <thead>
                    <tr className="text-sm leading-normal">
                        <th className="py-4 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light">
                            Avatar
                        </th>
                        <th className="py-4 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light">
                            Name
                        </th>
                        <th className="py-4 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light text-right">
                            Score
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {items?.map((item, index) => (
                        <tr
                            className="hover:bg-grey-lighter"
                            key={index}
                            onClick={() => {
                                onRowSelect(item);
                            }}
                        >
                            <td className="py-4 px-6 border-b border-grey-light">
                                <img
                                    src="https://via.placeholder.com/40"
                                    className="rounded-full h-10 w-10"
                                />
                            </td>
                            <td className="py-4 px-6 border-b border-grey-light">
                                {item.name}
                            </td>
                            <td className="py-4 px-6 border-b border-grey-light text-right">
                                {item.score}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export const ClassTable: React.FC<
    React.PropsWithChildren<StudentTableProps>
> = ({ onRowSelect, items, children }) => {
    return (
        <div className="f-tbl-container">
            <table className="w-full table-auto">
                <thead>
                    <tr className="text-sm leading-normal">
                        <th className="py-4 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light">
                            Avatar
                        </th>
                        <th className="py-4 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light">
                            Name
                        </th>
                        <th className="py-4 px-6 bg-grey-lightest font-bold uppercase text-sm text-grey-dark border-b border-grey-light text-right">
                            Score
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {items?.map((item, index) => (
                        <tr
                            className="hover:bg-grey-lighter"
                            key={index}
                            onClick={() => {
                                onRowSelect(item);
                            }}
                        >
                            <td className="py-4 px-6 border-b border-grey-light">
                                <img
                                    src="https://via.placeholder.com/40"
                                    className="rounded-full h-10 w-10"
                                />
                            </td>
                            <td className="py-4 px-6 border-b border-grey-light">
                                {item.name}
                            </td>
                            <td className="py-4 px-6 border-b border-grey-light text-right">
                                {item.score}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};
