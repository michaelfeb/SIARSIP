export function formatDate(
    value: string | Date | null | undefined,
    options: Intl.DateTimeFormatOptions = { day: '2-digit', month: 'short', year: 'numeric' },
    fallback = '—',
): string {
    if (!value) return fallback;

    const date = value instanceof Date ? value : new Date(value);

    return Number.isNaN(date.getTime()) ? fallback : new Intl.DateTimeFormat('id-ID', options).format(date);
}
