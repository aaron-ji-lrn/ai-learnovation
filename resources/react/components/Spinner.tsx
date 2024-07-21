import * as React from 'react';
const Spinner: React.FC<React.PropsWithChildren> = ({ children }) => {
    return (
        <div className="flex items-start">
            <div className="bg-blue-400 h-max w-max rounded-lg text-white font-bold hover:cursor-not-allowed duration-[500ms,800ms]">
                <div className="flex items-center justify-center m-[10px]">
                    <div className="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                    <div className="ml-2"> {children} </div>
                </div>
            </div>
        </div>
    );
};

export default Spinner;
