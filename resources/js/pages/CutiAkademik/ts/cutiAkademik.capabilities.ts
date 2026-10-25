import type { CutiAkademikActor, CutiAkademikLetter } from './cutiAkademik.types';

export function getCutiAkademikCapabilities(actor: CutiAkademikActor, letter: CutiAkademikLetter) {
    const isApplicant = actor.id === letter.applicant_id && actor.role_id === 1;
    const isReviewer = letter.status === 'IN_PROGRESS' && ['PRODI', 'AKADEMIK', 'KTU', 'DEKAN', 'RESEPSIONIS'].includes(letter.current_stage ?? '');

    return {
        canEdit: isApplicant && letter.status === 'DRAFT',
        canDelete: isApplicant && letter.status === 'DRAFT',
        canSubmit: isApplicant && letter.status === 'DRAFT',
        canReview: isReviewer && actor.role_id !== 1,
        canDownload: letter.status === 'ARCHIVED',
    };
}
