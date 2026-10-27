import Swal, { type SweetAlertIcon } from 'sweetalert2';

type AlertKind = 'success' | 'error' | 'info' | 'warning';

type AlertOptions = {
    title?: string;
    text: string;
    confirmButtonText?: string;
};

type ConfirmationOptions = AlertOptions & {
    variant?: 'info' | 'warning';
    cancelButtonText?: string;
    destructive?: boolean;
};

const variants: Record<AlertKind, { icon: SweetAlertIcon; title: string; iconColor: string }> = {
    success: { icon: 'success', title: 'Berhasil', iconColor: '#16a34a' },
    error: { icon: 'error', title: 'Gagal', iconColor: '#dc2626' },
    info: { icon: 'info', title: 'Informasi', iconColor: '#c2410c' },
    warning: { icon: 'warning', title: 'Konfirmasi', iconColor: '#ea580c' },
};

function showAlert(kind: AlertKind, options: AlertOptions) {
    const variant = variants[kind];

    return Swal.fire({
        icon: variant.icon,
        iconColor: variant.iconColor,
        title: options.title ?? variant.title,
        text: options.text,
        confirmButtonText: options.confirmButtonText ?? 'Mengerti',
        buttonsStyling: false,
        customClass: {
            popup: `swal-alert swal-alert--${kind}`,
            title: 'swal-alert-title',
            htmlContainer: 'swal-alert-content',
            actions: 'swal-actions-button-group',
            confirmButton: 'swal-confirm-button',
        },
    });
}

export function showSuccessAlert(options: AlertOptions) {
    return showAlert('success', options);
}

export function showErrorAlert(options: AlertOptions) {
    return showAlert('error', options);
}

export function showInfoAlert(options: AlertOptions) {
    return showAlert('info', options);
}

export function showConfirmationAlert(options: ConfirmationOptions) {
    const variant = options.variant ?? 'info';
    const alertVariant = variants[variant];

    return Swal.fire({
        icon: alertVariant.icon,
        iconColor: alertVariant.iconColor,
        title: options.title ?? alertVariant.title,
        text: options.text,
        showCancelButton: true,
        confirmButtonText: options.confirmButtonText ?? 'Lanjutkan',
        cancelButtonText: options.cancelButtonText ?? 'Batal',
        buttonsStyling: false,
        customClass: {
            popup: `swal-alert swal-alert--${variant}`,
            title: 'swal-alert-title',
            htmlContainer: 'swal-alert-content',
            actions: 'swal-actions-button-group',
            confirmButton: options.destructive ? 'swal-danger-button' : 'swal-confirm-button',
            cancelButton: 'swal-cancel-button',
        },
    });
}
