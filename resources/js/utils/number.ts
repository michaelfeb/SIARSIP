/** Return a number-like value as digits only, without relying on JavaScript number precision. */
export function toNumericString(value: string | number | null | undefined): string {
    return String(value ?? '').replace(/\D/g, '');
}

/** Format a digits-only value using Indonesian thousands separators. */
export function formatThousandSeparatedNumber(value: string | number | null | undefined): string {
    return toNumericString(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
