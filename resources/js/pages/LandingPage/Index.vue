<script setup lang="ts">
import { User, BadgeCheck, FileText, BookOpenIcon, ListChecksIcon } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { useBaseUrl } from '@/utils/useBaseUrl';
import { computed } from 'vue';

defineProps({
    templateSurat: Array,
});

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


</script>

<template>
    <div class="min-h-screen bg-gray-300 bg-repeat bg-center bg-auto flex flex-col items-center justify-center px-4 p-20"
        :style="{ backgroundImage: bgImage }">
        <div class="flex flex-col items-center mb-10 text-center">
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
        <div class="flex flex-col mt-5 md:flex-row gap-6 md:gap-8 items-center mb-5">
            <a href="https://docs.google.com/forms/d/e/1FAIpQLScbKorjuW0t6RXcuB9RwLuyxOjgW2AhKyFsRhgICztaMYmT6Q/viewform"
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <BookOpenIcon class="w-12 h-12 text-orange-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Bebas Ruang Baca</span>
                <label class="text-base text-sm text-gray-600">Link pendaftaraan</label>
            </a>
            <a href="https://docs.google.com/forms/d/1X2Qk4PPaxzycAhcRI4qfFvK3n6ec7ujSVU4LNO_orvc/viewform"
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <ListChecksIcon class="w-12 h-12 text-purple-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Mahasiswa Daftar Wisuda</span>
                <label class="text-base text-sm text-gray-600">Link verifikasi biodata</label>
            </a>
        </div>
    </div>

    <section id="template-surat" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-12">Template Surat</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="template in templateSurat" :key="template.id"
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
