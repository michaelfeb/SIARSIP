import type { TemplateSuratActor } from './templateSurat.types';

export function getTemplateSuratCapabilities(actor: TemplateSuratActor) {
    const canManage = actor.role_id !== 1;

    return { canCreate: canManage, canEdit: canManage, canDelete: canManage };
}
