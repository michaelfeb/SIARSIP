<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { router, useForm } from '@inertiajs/vue3'
import { onMounted, onUnmounted, ref } from 'vue';
import statusMapping from '@/utils/statusMapping'
import Swal from 'sweetalert2';

const props = defineProps<{
    berkasPersuratan: any,
    jenisSurat: any,
    mode: 'detail' | 'ajuan'
}>()

const show = ref(false)
const showModal = ref(false)

function onKeputusan() {
    showModal.value = true
}

function onCloseModal() {
    showModal.value = false
}

const form = useForm({
    note: '',
})


async function submitKeputusan(action: 'terima' | 'tolak', mode: 'biasa' | 'disposisi') {
    let statusBaru: number

    const currentStatus = props.berkasPersuratan.status
    const currentStage = parseInt(String(currentStatus).charAt(0))

    if (action === 'terima') {
        if (mode === 'disposisi') {
            statusBaru = 61
        } else {
            if (currentStage >= 7) {
                statusBaru = 72 // Kalau sudah di layanan, selesai
            } else {
                statusBaru = currentStatus + 10
            }
        }
    } else {
        // Kalau ditolak tetap biasa, stage jadi 3 di belakang
        statusBaru = parseInt(currentStage.toString() + '3')
    }

    await router.put(route('berkas-persuratan.keputusan', props.berkasPersuratan.id), {
        status: statusBaru,
        note: form.note
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false
            Swal.fire({
                title: 'Berhasil!',
                text: "Data telah diubah!",
                icon: 'success',
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'swal-confirm-button',
                },
            }).then(() => {
                router.get(route('berkas-persuratan.index'))
            });
        }
    })
}



const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Berkas Persuratan',
        href: '/berkas-persuratan',
    },
    {
        title: 'Detail',
        href: '/berkas-persuratan',
    },
]

function toBack() {
    router.visit(route('berkas-persuratan.index'))
}

function formatTanggal(tanggal: string) {
    if (!tanggal) return '-';
    const date = new Date(tanggal);
    return date.toLocaleDateString('id-ID', {
        weekday: 'long', // Senin, Selasa, etc
        day: '2-digit',
        month: 'long',   // Januari, Februari, etc
        year: 'numeric'
    });
}

onMounted(() => {

    setTimeout(() => {
        show.value = true
    }, 50)
})

console.log('====================================');
console.log();
console.log('====================================');

</script>

<template>
    <div v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 transition-opacity duration-300">
        <div class="bg-white w-full max-w-md p-6 rounded-lg shadow-xl relative">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Tanggapan</h2>
                <button @click="onCloseModal" class="text-gray-400 hover:text-gray-600 text-xl">
                    &times;
                </button>
            </div>

            <form @submit.prevent="" class="space-y-6">
                <textarea type="text" v-model="form.note"
                    placeholder="Opsional. Tulis catatan, saran atau revisi di sini"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
            </form>

            <div class="mt-6 flex justify-end gap-2">
                <button @click="submitKeputusan('tolak', 'biasa')"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Tolak
                </button>
                <button @click="submitKeputusan('terima', 'biasa')"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Terima
                </button>
                <button @click="submitKeputusan('terima', 'disposisi')"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Terima dan Disposisi
                </button>
            </div>
        </div>
    </div>

    <!-- Main -->
    <AppLayout :breadcrumbs="breadcrumbs">
        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="p-4 space-y-4">

                <h2 class="text-xl font-semibold">Detail Berkas Persuratan</h2>
                <table class="w-full table-auto border border-gray-300 mb-0">
                    <tbody class="text-sm">
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Nomor Surat</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasPersuratan.nomor_surat || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Mahasiswa</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ props.berkasPersuratan.user?.nama || '-' }} ({{ props.berkasPersuratan.user?.nim ||
                                    '-' }})
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Jenis Surat</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasPersuratan.jenis_surat?.nama ||
                                '-' }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Keterangan</td>
                            <td class="py-4 px-4 border border-gray-200">{{ props.berkasPersuratan.keterangan || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Tanggal Dikirim</td>
                            <td class="py-4 px-4 border border-gray-200">
                                {{ formatTanggal(props.berkasPersuratan.tanggal_dikirim) || '-' }}
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Status</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <div v-if="statusMapping[props.berkasPersuratan.status]"
                                    :class="`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-${statusMapping[props.berkasPersuratan.status].color}-100 text-${statusMapping[props.berkasPersuratan.status].color}-600`">
                                    <component :is="statusMapping[props.berkasPersuratan.status].icon"
                                        class="w-4 h-4 mr-1"
                                        :color="`${statusMapping[props.berkasPersuratan.status].colorIcon}`" />
                                    {{ statusMapping[props.berkasPersuratan.status].label }}
                                </div>
                            </td>
                        </tr>
                        <tr class="border-b">
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Berkas Mahasiswa</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <label v-if="JSON.parse(props.berkasPersuratan.berkas_mahasiswa) == null" for="">
                                    -
                                </label>
                                <ul v-else class="list-disc list-inside space-y-1">
                                    <li v-for="(file, index) in JSON.parse(props.berkasPersuratan.berkas_mahasiswa || '[]')"
                                        :key="index">
                                        <a :href="`/storage/${file}`" target="_blank"
                                            class="text-blue-600 hover:underline">
                                            Berkas {{ index + 1 }}
                                        </a>

                                        <span> | </span>
                                        <a :href="`/storage/${file}`" download class="text-blue-600 hover:underline">
                                            Download
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200">Surat Balasan</td>
                            <td class="py-4 px-4 border border-gray-200">
                                <label
                                    v-if="JSON.parse(props.berkasPersuratan.berkas_balasan) == null || JSON.parse(props.berkasPersuratan.berkas_balasan) == 0"
                                    for="">
                                    Tidak tersedia
                                </label>
                                <ul v-else class="list-disc list-inside space-y-1">
                                    <li v-for="(file, index) in JSON.parse(props.berkasPersuratan.berkas_balasan)"
                                        :key="index">
                                        <a :href="`/storage/${file}`" target="_blank"
                                            class="text-blue-600 hover:underline">
                                            Balasan {{ index + 1 }}
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-4 px-4 font-medium text-gray-600 border border-gray-200 align-top">Catatan
                            </td>
                            <td class="py-4 px-4 border border-gray-200">
                                <div v-if="props.berkasPersuratan.notes && props.berkasPersuratan.notes.length">
                                    <table class="text-sm w-full border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border px-3 py-2 text-left">Oleh</th>
                                                <th class="border px-3 py-2 text-left">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="note in props.berkasPersuratan.notes" :key="note.id">
                                                <td class="border px-3 py-2">{{ note.user?.nama || 'Tidak diketahui' }}
                                                </td>
                                                <td class="border px-3 py-2">{{ note.pesan }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-else>
                                    <table class="text-sm w-full border border-gray-300">
                                        <thead>
                                            <tr class="bg-gray-100">
                                                <th class="border px-3 py-2 text-left">Oleh</th>
                                                <th class="border px-3 py-2 text-left">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="border px-3 py-2"> - </td>
                                                <td class="border px-3 py-2"> - </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="w-full flex justify-end gap-2">
                    <Button type="button" @click="toBack"
                        class="mt-4 border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                        <span>Kembali</span>
                    </Button>

                    <Button v-if="props.mode === 'ajuan'" type="button" @click="onKeputusan"
                        class="mt-4 border border-blue-400 text-blue-500 hover:bg-blue-100 bg-white-500">
                        <span>Tambah Keputusan</span>
                    </Button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>