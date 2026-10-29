import type { CarouselActor } from './carousel.types';

export function getCarouselCapabilities(actor: CarouselActor) {
    const canManage = [6, 8].includes(actor.role_id);

    return { canCreate: canManage, canEdit: canManage, canDelete: canManage, canToggleStatus: canManage };
}
