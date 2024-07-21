import * as React from 'react';
// @ts-ignore
import ReactMarkdown from 'react-markdown';

const MarkdownContainer: React.FC<React.PropsWithChildren> = ({ children }) => {
    return <ReactMarkdown>{children}</ReactMarkdown>;
};

export default MarkdownContainer;
