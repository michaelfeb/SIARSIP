<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BadgeCheck,
    BookOpen,
    FileDown,
    FileText,
    GraduationCap,
    ListChecks,
    University,
    UserRound,
    UsersRound,
    X,
} from 'lucide-vue-next';
import { Carousel, Navigation, Pagination, Slide } from 'vue3-carousel';
import { computed, ref } from 'vue';
import { useBaseUrl } from '@/utils/useBaseUrl';
import 'vue3-carousel/carousel.css';

type TemplateSurat = {
    id: number;
    nama: string;
    deskripsi?: string | null;
};

type CarouselItem = {
    id: number;
    nama: string;
    gambar: string;
};

const iconComponents = {
    BookOpen,
    ListChecks,
    BadgeCheck,
    GraduationCap,
    University,
    FileText,
};

type LandingLink = {
    id: number;
    nama: string;
    deskripsi?: string | null;
    icon: keyof typeof iconComponents;
    color?: string | null;
    link: string;
};

const props = withDefaults(defineProps<{
    templateSurat?: TemplateSurat[];
    carousel?: CarouselItem[];
    linkLandingPage?: LandingLink[];
}>(), {
    templateSurat: () => [],
    carousel: () => [],
    linkLandingPage: () => [],
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const modalImage = ref<CarouselItem | null>(null);
const logoUrl = useBaseUrl('images/logo-ulm.png');

const carouselConfig = {
    itemsToShow: 1,
    wrapAround: true,
    autoplay: 5000,
    pauseAutoplayOnHover: true,
};

function redirectBasedOnLogin(mode: 'mahasiswa' | 'pegawai') {
    if (user.value?.role_id === 1) {
        return router.get(route('berkas-sidang-nol.index'));
    }

    if (user.value) {
        return router.get(route('dashboard'));
    }

    return router.get(route('login', { mode }));
}

function onLoginMahasiswa() {
    redirectBasedOnLogin('mahasiswa');
}

function onLoginPegawai() {
    redirectBasedOnLogin('pegawai');
}

function onOpenAnnouncement(item: CarouselItem) {
    modalImage.value = item;
}

function onCloseAnnouncement() {
    modalImage.value = null;
}

function carouselImageUrl(item: CarouselItem) {
    return useBaseUrl(`carousel-image/${item.gambar.split('/').pop()}`);
}
</script>

<template>
    <Head title="PORLAS FMIPA" />

    <main class="min-h-screen overflow-hidden bg-white text-slate-900">
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-[#7c2d12] via-[#c2410c] to-[#ea580c] px-4 sm:px-6">
            <div class="pointer-events-none absolute -right-28 -top-28 size-96 rounded-full border-[3rem] border-white/10" />
            <div class="pointer-events-none absolute -bottom-36 -left-24 size-80 rounded-full bg-white/10" />

            <div class="relative mx-auto max-w-6xl">
                <header class="flex items-center border-b border-white/20 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <img :src="logoUrl" alt="Logo Universitas Lambung Mangkurat" class="size-10 object-contain" />
                        <div>
                            <p class="text-sm font-bold tracking-wide">PORLAS FMIPA</p>
                            <p class="text-xs text-white/75">Aplikasi Persuratan</p>
                        </div>
                    </div>
                </header>

                <div class="grid items-center gap-10 py-14 lg:grid-cols-[minmax(0,1.25fr)_minmax(20rem,0.75fr)] lg:py-20">
                    <div class="text-white">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-100">Layanan Akademik Terpadu</p>
                        <h1 class="mt-4 max-w-2xl text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">Urus surat akademik, lebih sederhana.</h1>
                        <p class="mt-5 max-w-xl text-base leading-7 text-white/85 sm:text-lg">
                            PORLAS FMIPA membantu mahasiswa, dosen, dan pegawai mengakses layanan persuratan Fakultas MIPA Universitas Lambung Mangkurat dalam satu tempat.
                        </p>
                        <a href="#layanan" class="mt-8 inline-flex h-11 items-center gap-2 rounded-lg bg-white px-5 text-sm font-semibold text-[#9a3412] transition hover:bg-orange-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            Lihat layanan
                            <ArrowRight class="size-4" />
                        </a>
                    </div>

                    <aside class="rounded-2xl bg-white p-6 text-slate-900 shadow-2xl shadow-[#7c2d12]/25 sm:p-7">
                        <p class="text-sm font-semibold text-[#c2410c]">Mulai dari sini</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight">Pilih akses Anda</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Masuk sesuai dengan peran Anda untuk melanjutkan proses persuratan.</p>
                        <div class="mt-6 grid gap-3">
                            <button type="button" class="hero-role-button" @click="onLoginMahasiswa">
                                <UserRound class="size-5 text-[#c2410c]" />
                                <span>Login Mahasiswa</span>
                                <ArrowRight class="ml-auto size-4 text-slate-400" />
                            </button>
                            <button type="button" class="hero-role-button" @click="onLoginPegawai">
                                <UsersRound class="size-5 text-[#c2410c]" />
                                <span>Login Dosen / Pegawai</span>
                                <ArrowRight class="ml-auto size-4 text-slate-400" />
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <section id="layanan" class="relative scroll-mt-4 bg-[#fffaf7] px-4 py-14 sm:px-6 lg:py-20">
            <div class="relative mx-auto max-w-6xl">
                <div class="mb-9 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-sm font-semibold text-[#c2410c]">Akses layanan</p>
                        <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Semua kebutuhan dalam satu portal</h2>
                    </div>
                    <p class="max-w-md text-sm leading-6 text-slate-600">Pilih layanan yang Anda perlukan untuk memulai pengajuan atau mengunduh dokumen.</p>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <button type="button" class="landing-service-card" @click="onLoginMahasiswa">
                        <span class="landing-service-icon"><UserRound class="size-8" /></span>
                        <span class="landing-service-title">Login Mahasiswa</span>
                        <span class="landing-service-description">Portal pemberkasan online untuk mahasiswa melakukan permohonan surat akademik.</span>
                    </button>

                    <button type="button" class="landing-service-card" @click="onLoginPegawai">
                        <span class="landing-service-icon"><UsersRound class="size-8" /></span>
                        <span class="landing-service-title">Login Dosen / Pegawai</span>
                        <span class="landing-service-description">Portal bagi pegawai dan dosen untuk memproses permohonan surat akademik.</span>
                    </button>

                    <a href="#template-surat" class="landing-service-card">
                        <span class="landing-service-icon"><FileText class="size-8" /></span>
                        <span class="landing-service-title">Template Surat</span>
                        <span class="landing-service-description">Kumpulan template dan panduan pembuatan surat akademik.</span>
                    </a>

                    <a
                        v-for="item in props.linkLandingPage"
                        :key="item.id"
                        :href="item.link"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="landing-service-card"
                    >
                        <span class="landing-service-icon">
                            <component :is="iconComponents[item.icon] ?? FileText" class="size-8" />
                        </span>
                        <span class="landing-service-title">{{ item.nama }}</span>
                        <span class="landing-service-description">{{ item.deskripsi || 'Akses layanan administrasi akademik.' }}</span>
                    </a>
                </div>
            </div>
        </section>

        <section v-if="props.carousel.length" class="border-y border-orange-100 bg-white px-4 py-14 sm:px-6 lg:py-20">
            <div class="mx-auto max-w-6xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-slate-900">Pengumuman</h2>
                <p class="mt-2 text-sm text-slate-600 sm:text-base">Informasi terbaru dari Fakultas MIPA ULM.</p>

                <Carousel v-bind="carouselConfig" class="landing-carousel mx-auto mt-7 max-w-xl sm:mt-9">
                    <Slide v-for="item in props.carousel" :key="item.id">
                        <button type="button" class="block w-full cursor-zoom-in" :aria-label="`Lihat pengumuman ${item.nama}`" @click="onOpenAnnouncement(item)">
                            <img :src="carouselImageUrl(item)" :alt="item.nama" class="mx-auto max-h-[34rem] w-auto max-w-full rounded-lg bg-white object-contain shadow-md transition duration-200 hover:scale-[1.01]" />
                        </button>
                    </Slide>
                    <template #addons>
                        <Navigation />
                        <Pagination />
                    </template>
                </Carousel>
            </div>
        </section>

        <section id="template-surat" class="scroll-mt-4 px-4 py-14 sm:px-6 lg:py-20">
            <div class="mx-auto max-w-6xl">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-slate-900">Template Surat</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
                        Kumpulan template surat akademik yang dapat Anda unduh dan gunakan untuk keperluan administratif.
                    </p>
                </div>

                <div v-if="props.templateSurat.length" class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="template in props.templateSurat" :key="template.id" class="flex min-h-80 flex-col rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition-shadow hover:shadow-md">
                        <span class="mx-auto flex size-16 items-center justify-center rounded-2xl bg-orange-50 text-[#c2410c]"><FileText class="size-8" /></span>
                        <h3 class="mt-5 text-lg font-semibold leading-7 text-slate-900">{{ template.nama }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ template.deskripsi || 'Template surat akademik untuk kebutuhan administrasi.' }}</p>
                        <a :href="route('template-surat.download', template.id)" class="mt-auto inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#c2410c] px-4 text-sm font-medium text-white transition-colors hover:bg-[#9a3412] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#c2410c]">
                            <FileDown class="size-4" />
                            Download
                        </a>
                    </article>
                </div>

                <div v-else class="mt-9 rounded-2xl border border-dashed border-slate-300 px-6 py-12 text-center text-sm text-slate-500">
                    Template surat belum tersedia.
                </div>
            </div>
        </section>

        <footer class="bg-[#7c2d12] px-4 py-12 text-white sm:px-6">
            <div class="mx-auto grid max-w-6xl gap-9 md:grid-cols-3">
                <section>
                    <h2 class="text-base font-semibold">Tentang PORLAS FMIPA</h2>
                    <p class="mt-4 text-sm leading-6 text-white/85">PORLAS FMIPA adalah portal layanan surat akademik untuk memudahkan pengajuan dan pengelolaan surat di Fakultas MIPA Universitas Lambung Mangkurat.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold">Tautan Cepat</h2>
                    <nav class="mt-4 flex flex-col items-start gap-2.5 text-sm text-white/85">
                        <button type="button" class="transition hover:text-white" @click="onLoginMahasiswa">Portal Mahasiswa</button>
                        <button type="button" class="transition hover:text-white" @click="onLoginPegawai">Portal Pegawai</button>
                        <a href="#template-surat" class="transition hover:text-white">Template Surat</a>
                    </nav>
                </section>
                <section>
                    <h2 class="text-base font-semibold">Kontak</h2>
                    <address class="mt-4 text-sm leading-6 not-italic text-white/85"><strong class="font-semibold text-white">Fakultas MIPA ULM</strong><br>Universitas Lambung Mangkurat<br>Banjarmasin, Kalimantan Selatan</address>
                </section>
            </div>
            <div class="mx-auto mt-10 max-w-6xl border-t border-white/20 pt-6 text-center text-sm text-white/80">© {{ new Date().getFullYear() }} PORLAS FMIPA. Hak cipta dilindungi oleh Universitas Lambung Mangkurat.</div>
        </footer>

        <div v-if="modalImage" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4" role="dialog" aria-modal="true" aria-label="Preview pengumuman" @click.self="onCloseAnnouncement">
            <button type="button" class="absolute right-4 top-4 inline-flex size-10 items-center justify-center rounded-lg bg-white/10 text-white transition hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" aria-label="Tutup preview" @click="onCloseAnnouncement"><X class="size-5" /></button>
            <img :src="carouselImageUrl(modalImage)" :alt="modalImage.nama" class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl">
        </div>
    </main>
</template>

<style scoped>
.landing-service-card {
    display: flex;
    min-height: 18rem;
    flex-direction: column;
    align-items: center;
    border: 1px solid #fed7aa;
    border-radius: 1rem;
    background: white;
    padding: 1.75rem 1.5rem;
    cursor: pointer;
    text-align: center;
    box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
    transition: transform 200ms, border-color 200ms, box-shadow 200ms;
}

.landing-service-card:hover {
    transform: translateY(-0.5rem) scale(1.015);
    border-color: #c2410c;
    background: #c2410c;
    box-shadow: 0 20px 25px -5px rgb(124 45 18 / 0.25), 0 8px 10px -6px rgb(124 45 18 / 0.2);
}

.landing-service-card:hover .landing-service-icon {
    background: rgb(255 255 255 / 0.16);
    color: white;
}

.landing-service-card:hover .landing-service-title,
.landing-service-card:hover .landing-service-description {
    color: white;
}

.landing-service-card:focus-visible {
    outline: 2px solid #c2410c;
    outline-offset: 4px;
}

.landing-service-icon {
    display: flex;
    width: 4rem;
    height: 4rem;
    align-items: center;
    justify-content: center;
    border-radius: 1rem;
    background: #fff7ed;
    color: #c2410c;
}

.landing-service-title {
    margin-top: 1.25rem;
    color: #0f172a;
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1.75rem;
}

.landing-service-description {
    margin-top: 1rem;
    color: #475569;
    font-size: 0.875rem;
    line-height: 1.5rem;
}

:deep(.landing-carousel .carousel__viewport) {
    overflow: visible;
}

:deep(.landing-carousel .carousel__prev),
:deep(.landing-carousel .carousel__next) {
    width: 2.5rem;
    height: 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 9999px;
    background: white;
    color: #334155;
    box-shadow: 0 1px 2px rgb(15 23 42 / 0.08);
    transition: background-color 150ms;
}

:deep(.landing-carousel .carousel__prev:hover),
:deep(.landing-carousel .carousel__next:hover) { background: #f8fafc; }

:deep(.landing-carousel .carousel__prev) { left: -3.5rem; }
:deep(.landing-carousel .carousel__next) { right: -3.5rem; }

:deep(.landing-carousel .carousel__pagination) { margin-top: 1.25rem; gap: 0.5rem; }
:deep(.landing-carousel .carousel__pagination-button::after) { width: 0.5rem; height: 0.5rem; border-radius: 9999px; background: #cbd5e1; }
:deep(.landing-carousel .carousel__pagination-button--active::after) { background: #c2410c; }

.hero-role-button {
    display: flex;
    width: 100%;
    cursor: pointer;
    align-items: center;
    gap: 0.75rem;
    border: 1px solid #fed7aa;
    border-radius: 0.5rem;
    padding: 0.875rem 1rem;
    color: #1e293b;
    font-size: 0.875rem;
    font-weight: 600;
    text-align: left;
    transition: border-color 150ms, background-color 150ms;
}

.hero-role-button:hover {
    border-color: #fdba74;
    background: #fff7ed;
}

.hero-role-button:focus-visible {
    outline: 2px solid #c2410c;
    outline-offset: 2px;
}

@media (max-width: 640px) {
    :deep(.landing-carousel .carousel__prev) { left: 0.5rem; }
    :deep(.landing-carousel .carousel__next) { right: 0.5rem; }
}
</style>

<style>
html { scroll-behavior: smooth; }
</style>
