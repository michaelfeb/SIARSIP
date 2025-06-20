<script setup lang="ts">
import { User, BadgeCheck, FileText, University, GraduationCap, BookOpen, ListChecks } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { useBaseUrl } from '@/utils/useBaseUrl';
import { computed, ref } from 'vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import 'vue3-carousel/carousel.css'

const props = defineProps({
    templateSurat: Array,
    carousel: Array,
    linkLandingPage: Array,
});

const Lucide = {
    BookOpen,
    ListChecks,
    BadgeCheck,
    GraduationCap,
    University,
    FileText
}

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)

function redirectBasedOnLogin(mode: 'mahasiswa' | 'pegawai') {
    if (user.value?.role_id === 1) return router.get(route('berkas-persuratan.index'));
    if (user.value) return router.get(route('dashboard'));
    return router.get(route('login', { mode }));
}

const logoUrl = useBaseUrl('images/logo-ulm.png')
const bgImage = `url('${useBaseUrl('images/background-landing-page-3.png')}')`

function onLoginMahasiswa() {
    redirectBasedOnLogin('mahasiswa');
}

function onLoginPegawai() {
    redirectBasedOnLogin('pegawai');
}

const carouselItems = ref(props.carousel)
const modalImage = ref(null)

function openImage(item) {
    modalImage.value = item
}

const config = {
    itemsToShow: 2,
    wrapAround: true,
    autoplay: 4000,
    pauseAutoplayOnHover: true,
    breakpoints: {
        1024: {
            itemsToShow: 2,
            height: 600
        },
        768: {
            itemsToShow: 1,
            height: 450,
            gap: 10,
        },
        480: {
            itemsToShow: 1,
            height: 400,
            gap: 10,
        }
    }
}


</script>

<style scoped>
.carousel-custom>>>.carousel__slide {
    transition: transform 0.3s ease-in-out;
}
</style>

<template>
    <div class="min-h-screen bg-gray-300 bg-repeat bg-center bg-auto flex flex-col items-center justify-center px-4 p-20"
        :style="{ backgroundImage: bgImage }">
        <div class="flex flex-col items-center mb-10 text-center">
            <img :src="logoUrl" alt="Logo" class="w-40 h-40 mb-4" />
            <h1 class="text-3xl font-bold text-gray-800">PORLAS FMIPA</h1>
            <label class="text-base text-gray-600">Portal Layanan Surat Akademik FMIPA ULM</label>
        </div>
        <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-center">
            <div @click="onLoginMahasiswa"
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <User class="w-12 h-12 text-blue-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Login Mahasiswa</span>
                <label class="text-base text-sm text-gray-600">Portal pemberkasan online</label>
            </div>
            <div @click="onLoginPegawai"
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <BadgeCheck class="w-12 h-12 text-green-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Login Pegawai</span>
                <label class="text-base text-sm text-gray-600">Akses login untuk Pegawai FMIPA</label>
            </div>
            <a href="#template-surat"
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <FileText class="w-12 h-12 text-purple-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Template Surat</span>
                <label class="text-base text-sm text-gray-600">Template berkas surat akademik untuk mahasiswa</label>
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 mt-5 mb-5 place-items-center">
            <template v-for="(item, index) in linkLandingPage" :key="index">
                <a :href="item.link" target="_blank"
                    class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                    <component :is="Lucide[item.icon]" :class="['w-12 h-12 mb-4', item.color]" />
                    <span class="text-lg font-semibold text-gray-700">{{ item.nama }}</span>
                    <label class="text-sm text-gray-600">{{ item.deskripsi }}</label>
                </a>
            </template>
        </div>

    </div>

    <section class="mb-10">
        <div class="relative w-full max-w-5xl mx-auto mt-8 text-center">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-0 sm:mb-0 md:mb-5">Pengumuman</h2>
            <Carousel v-bind="config">
                <Slide v-for="(item, index) in carouselItems" :key="index">
                    <div @click="openImage(item)" class="cursor-pointer">
                        <img :src="useBaseUrl(`carousel-image/${item.gambar.split('/').pop()}`)" :alt="item.nama"
                            class="w-100 transition-transform duration-200 ease-in-out hover:scale-105" />
                    </div>
                </Slide>
                <template #addons>
                    <Navigation />
                    <Pagination />
                </template>
            </Carousel>

            <!-- Modal preview -->
            <div v-if="modalImage"
                class="fixed inset-0 z-50 bg-black bg-opacity-80 flex items-center justify-center px-4">
                <img :src="useBaseUrl(`carousel-image/${modalImage.gambar.split('/').pop()}`)"
                    class="w-full max-w-[90vw] max-h-[90vh] h-auto rounded-xl shadow-xl object-contain" />
                <button @click="modalImage = null"
                    class="absolute top-5 right-5 text-white text-3xl font-bold">×</button>
            </div>
        </div>
    </section>

    <section id="template-surat" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-12">Template Surat</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="template in props.templateSurat" :key="template.id"
                    class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center hover:shadow-lg transition">
                    <FileText class="w-12 h-12 text-purple-600 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ template.nama }}</h3>
                    <p class="text-gray-500 text-sm mb-4">{{ template.deskripsi ?? 'Tidak ada deskripsi' }}</p>
                    <a :href="route('template-surat.download', template.id)"
                        class="mt-auto inline-block px-4 py-2 bg-yellow-400 text-white rounded hover:bg-yellow-500 text-sm font-medium transition">
                        Download
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

<style>
html {
    scroll-behavior: smooth;
}
</style>
