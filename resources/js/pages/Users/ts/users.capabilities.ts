export function canResetUserPassword(roleId: number): boolean {
    return [6, 8].includes(roleId);
}
