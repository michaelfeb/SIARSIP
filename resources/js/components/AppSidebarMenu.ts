import { Folders, LayoutGrid, Mail, Minus, Settings, User } from 'lucide-vue-next';

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

export const sidebars = {
    1: [
        sidangNol,
        {
            title: 'Template Surat',
            href: '/template-surat-mahasiswa',
            icon: Folders,
        },
    ],
    2: [dashboard, templateSurat, pengguna],
    3: [dashboard, templateSurat, pengguna],
    4: [dashboard, templateSurat, pengguna],
    5: [dashboard, templateSurat, pengguna],
    6: [
        dashboard,
        sidangNol,
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
};
