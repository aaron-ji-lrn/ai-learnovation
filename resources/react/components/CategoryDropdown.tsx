import * as React from 'react';

interface CategoryProps {
    items: any[];
    label: string;
    onSelect: (item: any) => void;
}

const CategoryDropdown: React.FC<React.PropsWithChildren<CategoryProps>> = ({
    onSelect,
    label,
    items,
}) => {
    const [isOpen, setIsOpen] = React.useState(false);

    return (
        <div className="dropdown category relative group pr-4">
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
                    {items?.map((item, index) => (
                        <div
                            key={index}
                            className="inline-flex w-full p-4 mt-2 text-gray-700 hover:bg-gray-100 active:bg-blue-100 cursor-pointer rounded-md border-b-2 border-gray-3"
                            onClick={() => {
                                onSelect(item);
                                setIsOpen(false);
                            }}
                        >
                            <div style={{ width: 200 }}>{item.name}</div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default CategoryDropdown;
