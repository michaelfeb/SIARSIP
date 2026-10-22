/**
 * Requests a minimal, document-only browser PDF view when the viewer supports
 * standard PDF URL fragment parameters.
 */
export function sidangNolPdfEmbedUrl(url: string): string {
    const fragmentSeparator = url.includes('#') ? '&' : '#';

    return `${url}${fragmentSeparator}toolbar=0&navpanes=0&scrollbar=0&view=FitH`;
}
