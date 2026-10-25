import type { CutiAkademikActor, CutiAkademikLetter } from './cutiAkademik.types';

export function getCutiAkademikCapabilities(actor: CutiAkademikActor, letter: CutiAkademikLetter) {
    const isSuperAdmin = actor.role_id === 8;
    const isApplicant = actor.id === letter.applicant_id && actor.role_id === 1;
    const isReviewer = letter.status === 'IN_PROGRESS' && ['PRODI', 'AKADEMIK', 'KTU', 'DEKAN', 'RESEPSIONIS'].includes(letter.current_stage ?? '');

    return {
        canEdit: letter.status === 'DRAFT' && (isApplicant || isSuperAdmin),
        canDelete: letter.status === 'DRAFT' && (isApplicant || isSuperAdmin),
        canSubmit: letter.status === 'DRAFT' && (isApplicant || isSuperAdmin),
        canReview: isReviewer && (actor.role_id !== 1 || isSuperAdmin),
        canDownload: letter.status === 'ARCHIVED',
    };
}
