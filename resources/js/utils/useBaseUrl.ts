export function useBaseUrl(path: string): string {
    const base = import.meta.env.BASE_URL || '/';
    return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}

export function removeBasePrefix(path: string): string {
  const base = import.meta.env.BASE_URL || '/'
  return path.startsWith(base) ? path.slice(base.length - 1) : path
}