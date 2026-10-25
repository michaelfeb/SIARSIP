import { Folders, GraduationCap, LayoutGrid, Mail, Minus, Settings, User } from 'lucide-vue-next';

const dashboard = {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
};
const templateSurat = {
    title: 'Template Surat',
    href: '/template-surat',
    icon: Folders,
};

const pengguna = {
    title: 'Pengguna',
    href: '/users',
    icon: User,
};

const sidangNol = {
    title: 'Berkas',
    icon: Mail,
    children: [{ title: 'Berkas Sidang Nol', icon: Minus, href: '/berkas-sidang-nol' }],
};

const akademik = {
    title: 'Akademik',
    icon: GraduationCap,
    children: [{ title: 'Cuti Akademik', icon: Minus, href: '/cuti-akademik' }],
};

export const sidebars = {
    1: [
        sidangNol,
        akademik,
        {
            title: 'Template Surat',
            href: '/template-surat-mahasiswa',
            icon: Folders,
        },
    ],
    2: [dashboard, akademik, templateSurat, pengguna],
    3: [dashboard, akademik, templateSurat, pengguna],
    4: [dashboard, templateSurat, pengguna],
    5: [dashboard, akademik, templateSurat, pengguna],
    6: [
        dashboard,
        sidangNol,
        akademik,
        templateSurat,
        pengguna,
        {
            title: 'Pengaturan',
            icon: Settings,
            children: [
                { title: 'Carousel', icon: Minus, href: '/carousel' },
                { title: 'Link Landing Page', icon: Minus, href: '/link-landing-page' },
            ],
        },
    ],
    7: [dashboard, templateSurat, pengguna],
    8: [
        dashboard,
        sidangNol,
        akademik,
        templateSurat,
        pengguna,
        {
            title: 'Pengaturan',
            icon: Settings,
            children: [
                { title: 'Carousel', icon: Minus, href: '/carousel' },
                { title: 'Link Landing Page', icon: Minus, href: '/link-landing-page' },
            ],
        },
    ],
    9: [dashboard],
    12: [dashboard, akademik, templateSurat, pengguna],
};
