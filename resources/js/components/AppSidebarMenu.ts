import { Folders, LayoutGrid, Mail, Minus, Settings, User } from 'lucide-vue-next';

export const sidebars = {
    1: [
        // Role ID 1: Mahasiswa
        {
            title: 'Berkas',
            icon: Mail,
            children: [
                { title: 'Berkas Sidang Nol', icon: Minus, href: '/berkas-sidang-nol' },
                { title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' },
            ],
        },
        {
            title: 'Template Surat',
            href: '/template-surat-mahasiswa',
            icon: Folders,
        },
    ],

    2: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [
                { title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' },
            ],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },
        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],

    3: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [{ title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' }],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },
        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],

    4: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [{ title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' }],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },
        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],

    5: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [{ title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' }],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },
        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],

    6: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [
                { title: 'Berkas Sidang Nol', icon: Minus, href: '/berkas-sidang-nol' },
                { title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' },
            ],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },
        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],

    7: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [{ title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' }],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },
        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],

    8: [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Berkas',
            icon: Mail,
            children: [
                { title: 'Berkas Sidang Nol', icon: Minus, href: '/berkas-sidang-nol' },
                { title: 'Berkas Persuratan', icon: Minus, href: '/berkas-persuratan' },
            ],
        },
        {
            title: 'Template Surat',
            href: '/template-surat',
            icon: Folders,
        },

        {
            title: 'Pengguna',
            href: '/users',
            icon: User,
        },

        {
            title: 'Pengaturan',
            icon: Settings,
            children: [{ title: 'Jenis Surat', icon: Minus, href: '/jenis-surat' }],
        },
    ],
};
