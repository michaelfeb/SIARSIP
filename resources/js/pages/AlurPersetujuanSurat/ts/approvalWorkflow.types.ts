export interface ApprovalWorkflowLetterType {
    id: number;
    code: string;
    name: string;
    approvalChain: string[];
    notesMarkdown: string | null;
    notesHtml: string;
    activeLettersCount: number;
}
