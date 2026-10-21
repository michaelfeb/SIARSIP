<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { Select as VueSelect } from 'vue3-select-component';
import SidangNolDocumentUpload from './components/SidangNolDocumentUpload.vue';
import { SIDANG_NOL_DOCUMENTS, SIDANG_NOL_STATUS } from './ts/constants';
import type { SidangNol, SidangNolAuth, SidangNolDocumentKey } from './ts/types';
import { useSidangNolDocuments } from './ts/useSidangNolDocuments';

interface UserOption {
    label: string;
    value: number;
}

type DocumentFormValue = File | string | null;

interface SidangNolFormData {
    user_id: number | '';
    nomor_surat: string;
    dokumen_hasil_studi: DocumentFormValue;
    dokumen_data_diri: DocumentFormValue;
    dokumen_pddikti_ukt: DocumentFormValue;
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: DocumentFormValue;
    dokumen_office_toefl: DocumentFormValue;
    dokumen_tambahan: DocumentFormValue;
    status: number;
    tanggal_dikirim: string;
    tanggal_selesai: string;
}

const props = defineProps<{
    auth: SidangNolAuth;
    berkasSidangNol?: SidangNol;
    mode: 'create' | 'edit';
}>();

const today = new Date().toISOString().slice(0, 10);
const isEdit = computed(() => props.mode === 'edit');
const isStudent = computed(() => props.auth.user.role_id === 1);
const userSelected = ref<number | null>(null);
const userOptions = ref<UserOption[]>([]);
const filepondFiles = reactive<Record<SidangNolDocumentKey, unknown[]>>({
    dokumen_hasil_studi: [],
    dokumen_data_diri: [],
    dokumen_pddikti_ukt: [],
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: [],
    dokumen_office_toefl: [],
    dokumen_tambahan: [],
});
const { documentUrls, loadDocumentUrls } = useSidangNolDocuments();

let userSearchTimer: ReturnType<typeof setTimeout> | undefined;

const form = useForm<SidangNolFormData>({
    user_id: props.berkasSidangNol?.user_id ?? '',
    nomor_surat: props.berkasSidangNol?.nomor_surat ?? '',
    dokumen_hasil_studi: props.berkasSidangNol?.dokumen_hasil_studi ?? null,
    dokumen_data_diri: props.berkasSidangNol?.dokumen_data_diri ?? null,
    dokumen_pddikti_ukt: props.berkasSidangNol?.dokumen_pddikti_ukt ?? null,
    dokumen_ruangbaca_laboratorium_pkkmb_skpi: props.berkasSidangNol?.dokumen_ruangbaca_laboratorium_pkkmb_skpi ?? null,
    dokumen_office_toefl: props.berkasSidangNol?.dokumen_office_toefl ?? null,
    dokumen_tambahan: props.berkasSidangNol?.dokumen_tambahan ?? null,
    status: props.berkasSidangNol?.status ?? 0,
    tanggal_dikirim: props.berkasSidangNol?.tanggal_dikirim?.slice(0, 10) ?? today,
    tanggal_selesai: props.berkasSidangNol?.tanggal_selesai?.slice(0, 10) ?? '',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Berkas Sidang Nol', href: '/berkas-sidang-nol' },
    { title: isEdit.value ? 'Ubah' : 'Tambah', href: '/berkas-sidang-nol' },
];

function selectUser(user: SidangNol['user'] | SidangNolAuth['user']) {
    if (!user) {
        return;
    }

    const option = { label: `${user.nama} (${user.nim ?? '-'})`, value: user.id };
    userOptions.value = [option];
    userSelected.value = option.value;
    form.user_id = option.value;
}

function onUserSelected(option: UserOption | null) {
    form.user_id = option?.value ?? '';
}

function onUserOptionSearch(searchText: string) {
    if (userSearchTimer) {
        clearTimeout(userSearchTimer);
    }

    if (searchText.trim().length < 3) {
        userOptions.value = [];
        return;
    }

    userSearchTimer = setTimeout(async () => {
        try {
            const response = await axios.get<Array<Pick<SidangNolAuth['user'], 'id' | 'nama' | 'nim'>>>(
                route('api.users.search-user', { search: searchText }),
            );
            userOptions.value = response.data.map((user) => ({
                label: `${user.nama} (${user.nim ?? '-'})`,
                value: user.id,
            }));
        } catch {
            userOptions.value = [];
        }
    }, 250);
}

function updateDocumentFiles(files: unknown[], key: SidangNolDocumentKey) {
    filepondFiles[key] = files;
    const firstFile = files[0] as { file?: File } | undefined;
    form[key] = firstFile?.file ?? null;
}

async function loadExistingDocuments() {
    if (!props.berkasSidangNol) {
        return;
    }

    const urls = await loadDocumentUrls(props.berkasSidangNol.id);
    for (const document of SIDANG_NOL_DOCUMENTS) {
        const url = urls[document.key];
        if (url) {
            filepondFiles[document.key] = [{ source: url, options: { type: 'remote' } }];
        }
    }
}

function submit() {
    const url = isEdit.value
        ? route('berkas-sidang-nol.saveEdit', props.berkasSidangNol?.id)
        : route('berkas-sidang-nol.saveCreate');

    form.post(url, {
        forceFormData: true,
        onSuccess: () => Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: isEdit.value ? 'Data telah diperbarui.' : 'Data telah ditambahkan.',
        }),
        onError: () => Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Periksa kembali data dan dokumen yang diunggah.',
        }),
    });
}

function backToIndex() {
    router.get(route('berkas-sidang-nol.index'));
}

onMounted(async () => {
    if (isStudent.value) {
        selectUser(props.auth.user);
    } else if (props.berkasSidangNol?.user) {
        selectUser(props.berkasSidangNol.user);
    }

    if (isEdit.value) {
        await loadExistingDocuments();
    }
});

onBeforeUnmount(() => {
    if (userSearchTimer) {
        clearTimeout(userSearchTimer);
    }
});
</script>

<template>
    <Head :title="isEdit ? 'Ubah Berkas Sidang Nol' : 'Tambah Berkas Sidang Nol'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <div>
                <h1 class="text-xl font-semibold">{{ isEdit ? 'Edit Berkas Sidang Nol' : 'Tambah Berkas Sidang Nol' }}</h1>
                <p class="mt-1 text-sm text-gray-500">Unggah setiap dokumen dalam format PDF dengan ukuran maksimal 1 MB.</p>
            </div>

            <div v-if="Object.keys(form.errors).length" class="rounded bg-red-100 p-4 text-red-600">
                <ul class="list-disc space-y-1 pl-5 text-sm"><li v-for="(error, key) in form.errors" :key="key">{{ error }}</li></ul>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label for="nomor_surat">Nomor Surat</Label>
                    <Input id="nomor_surat" v-model="form.nomor_surat" type="text" placeholder="Nomor surat akan diisi oleh operator" :disabled="!([2, 6, 7, 8].includes(auth.user.role_id))" />
                    <InputError :message="form.errors.nomor_surat" />
                </div>

                <div class="space-y-2">
                    <Label for="user_selected">Mahasiswa <span class="text-red-500">*</span></Label>
                    <VueSelect id="user_selected" v-model="userSelected" :options="userOptions" editable placeholder="Cari mahasiswa" :is-disabled="isStudent" @search="onUserOptionSearch" @option-selected="onUserSelected" />
                    <InputError :message="form.errors.user_id" />
                </div>

                <SidangNolDocumentUpload
                    v-for="document in SIDANG_NOL_DOCUMENTS"
                    :key="document.key"
                    :document="document"
                    :files="filepondFiles[document.key]"
                    :error="form.errors[document.key]"
                    @update="updateDocumentFiles($event, document.key)"
                />

                <div class="space-y-2">
                    <Label for="status">Status</Label>
                    <select id="status" v-model="form.status" disabled class="w-full rounded border border-gray-300 bg-gray-100 px-3 py-2 text-sm">
                        <option v-for="(status, code) in SIDANG_NOL_STATUS" :key="code" :value="Number(code)">{{ status.label }}</option>
                    </select>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="tanggal_dikirim">Tanggal Dikirim</Label>
                        <Input id="tanggal_dikirim" v-model="form.tanggal_dikirim" type="date" readonly />
                    </div>
                    <div class="space-y-2">
                        <Label for="tanggal_selesai">Tanggal Selesai</Label>
                        <Input id="tanggal_selesai" v-model="form.tanggal_selesai" type="date" readonly />
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Button type="button" class="border border-orange-400 bg-white text-orange-500 hover:bg-orange-100" @click="backToIndex">Kembali</Button>
                    <Button type="submit" class="bg-blue-500 text-white hover:bg-blue-600" :disabled="form.processing">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
