import * as React from 'react';

interface StudentTableProps {
    items: any[];
    label: string;
    onSelect: (item: any) => void;
    placeholder: string;
}

const StudentDropdown: React.FC<React.PropsWithChildren<StudentTableProps>> = ({
    onSelect,
    label,
    items,
    placeholder,
}) => {
    const [isOpen, setIsOpen] = React.useState(false);
    const [searchString, setSearchString] = React.useState('');

    return (
        <div className="dropdown relative group pr-4">
            <button
                onClick={() => {
                    setIsOpen(!isOpen);
                }}
                className="flex justify-between text-left p-4 text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <span className="font-bold">{label}</span>
                <span className="fa-solid fa-caret-down"></span>
            </button>
            {isOpen && (
                <div className="dropdown-items absolute left-0 mt-2 rounded-md shadow-lg bg-white ring-1 ring-gray-700 space-y-1 w-8/12">
                    <input
                        className="block w-5/12 m-4 p-4 text-gray-800 border rounded-md  border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4"
                        type="text"
                        placeholder={placeholder}
                        onChange={(e) => setSearchString(e.target.value)}
                        autoComplete="off"
                    />
                    <hr className="border-gray-200 dark:border-gray-700 " />
                    <div className="inline-flex w-full px-4 py-2 mt-2 rounded-md border-b-2 border-gray-3">
                        <div
                            className="text-sm text-gray-700"
                            style={{ width: 200 }}
                        >
                            Name
                        </div>
                        <div className="text-sm text-gray-700">
                            No. of assessments
                        </div>
                    </div>
                    {items
                        ?.filter((item) => {
                            return (
                                searchString === '' ||
                                item.name
                                    .toLowerCase()
                                    .includes(searchString.toLowerCase())
                            );
                        })
                        .map((item, index) => (
                            <div
                                key={index}
                                className="inline-flex w-full p-4 mt-2 text-gray-700 hover:bg-gray-100 active:bg-blue-100 cursor-pointer rounded-md border-b-2 border-gray-3"
                                onClick={() => {
                                    onSelect(item);
                                    setIsOpen(false);
                                }}
                            >
                                <div style={{ width: 200 }}>
                                    {item.name}{' '}
                                    <span className="text-gray-400">
                                        ({item.id})
                                    </span>
                                </div>

                                <div>{item.assessments.length}</div>
                            </div>
                        ))}
                </div>
            )}
        </div>
    );
};

export default StudentDropdown;
