import { CheckCircle, HelpCircle, Pencil, XCircle } from 'lucide-vue-next';

export const statusMappingSidangNol: Record<
    number,
    {
        label: string;
        color: string;
        colorIcon: string;
        icon: any;
    }
> = {
    0: { label: 'Draft', color: 'gray', colorIcon: "gray", icon: Pencil },
    1: { label: 'Menunggu', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    2: { label: 'Diterima', color: 'green', colorIcon: "green", icon: CheckCircle },
    3: { label: 'Ditolak', color: 'red', colorIcon: "red", icon: XCircle },
}

export default statusMappingSidangNol;
