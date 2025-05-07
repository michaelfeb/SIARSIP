import { CheckCircle, HelpCircle, Pencil, XCircle } from 'lucide-vue-next';

export const statusMapping: Record<
    number,
    {
        label: string;
        color: string;
        colorIcon: string;
        icon: any;
    }
> = {
    11: { label: 'Draft', color: 'gray', colorIcon: "gray", icon: Pencil },
    21: { label: 'Menunggu Resepsionis', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    22: { label: 'Diterima Resepsionis', color: 'green', colorIcon: "green", icon: CheckCircle },
    23: { label: 'Ditolak Resepsionis', color: 'red', colorIcon: "red", icon: XCircle },
    31: { label: 'Menunggu Dekan', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    32: { label: 'Diterima Dekan', color: 'green', colorIcon: "green", icon: CheckCircle },
    33: { label: 'Ditolak Dekan', color: 'red', colorIcon: "red", icon: XCircle },
    41: { label: 'Menunggu Wakil Dekan', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    42: { label: 'Diterima Wakil Dekan', color: 'green', colorIcon: "green", icon: CheckCircle },
    43: { label: 'Ditolak Wakil Dekan', color: 'red', colorIcon: "red", icon: XCircle },
    51: { label: 'Menunggu Kepala TU', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    52: { label: 'Diterima Kepala TU', color: 'green', colorIcon: "green", icon: CheckCircle },
    53: { label: 'Ditolak Kepala TU', color: 'red', colorIcon: "red", icon: XCircle },
    61: { label: 'Menunggu Sub Akademik', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    62: { label: 'Diterima Sub Akademik', color: 'green', colorIcon: "green", icon: CheckCircle },
    63: { label: 'Ditolak Sub Akademik', color: 'red', colorIcon: "red", icon: XCircle },
    71: { label: 'Menunggu TTD', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    81: { label: 'Menunggu Sub Layanan', color: 'blue', colorIcon: "blue", icon: HelpCircle },
    91: { label: 'Selesai', color: 'green', colorIcon: "green", icon: CheckCircle },
};

export default statusMapping;
