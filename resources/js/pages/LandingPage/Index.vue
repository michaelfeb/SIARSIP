<script setup lang="ts">
import { User, BadgeCheck, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

defineProps({
  templateSurat: Array
});

</script>

<template>
    <div
        class="min-h-screen bg-white bg-[url('/images/background-landing-page.png')] bg-repeat bg-center bg-auto flex flex-col items-center justify-center px-4">
        <div class="flex flex-col items-center mb-10 text-center">
            <img src="/images/logo-ulm.png" alt="Logo" class="w-40 h-40 mb-4" />
            <h1 class="text-3xl font-bold text-gray-800">SIARSIP</h1>
            <label class="text-base text-gray-600">Sistem Informasi Layanan Surat Akademik FMIPA ULM</label>
        </div>
        <div class="flex flex-col md:flex-row gap-6 md:gap-8 items-center">
            <div
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <User class="w-12 h-12 text-blue-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Login Mahasiswa</span>
                <label class="text-base text-sm text-gray-600">Portal pemberkasan online</label>
            </div>
            <div
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <BadgeCheck class="w-12 h-12 text-green-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Login Staff</span>
                <label class="text-base text-sm text-gray-600">Akses login untuk sraff FMIPA</label>
            </div>
            <a href="#template-surat"
                class="w-60 h-60 bg-white shadow-md rounded-xl flex flex-col items-center justify-center text-center border border-gray-200 hover:bg-yellow-300 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer p-4">
                <FileText class="w-12 h-12 text-purple-600 mb-4" />
                <span class="text-lg font-semibold text-gray-700">Template Surat</span>
                <label class="text-base text-sm text-gray-600">Template berkas surat akademik untuk mahasiswa</label>
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
