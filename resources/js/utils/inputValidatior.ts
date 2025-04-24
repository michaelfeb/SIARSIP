export function onlyAllowNumbers(e: KeyboardEvent): void {
    const char = String.fromCharCode(e.which ?? e.keyCode);
    if (!/[0-9]/.test(char)) {
        e.preventDefault();
    }
}
