<script setup lang="ts">
import DataTable from '@/components/data-table/DataTable.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Eye, Pencil, Search, Trash2, KeyRound } from 'lucide-vue-next';
import { ref, watch, onBeforeUnmount } from 'vue';
import type { UserFilters, UserPage, UserQuery, UserRecord } from './ts/users.types';
const p = defineProps<{ users: UserPage<UserRecord>; filters: UserFilters; loading: boolean; canReset: boolean }>();
const e = defineEmits<{ query: [q: UserQuery]; view: [u: UserRecord]; edit: [u: UserRecord]; delete: [u: UserRecord]; reset: [u: UserRecord] }>();
const search = ref(p.filters.search ?? '');
const audience = ref(p.filters.audience);
const o = ref({ page: p.users.current_page, rowsPerPage: p.users.per_page });
let t: ReturnType<typeof setTimeout>;
watch(
    () => p.filters,
    (f) => {
        search.value = f.search ?? '';
        audience.value = f.audience;
    },
);
watch(
    () => [o.value.page, o.value.rowsPerPage],
    () => send(),
);
onBeforeUnmount(() => clearTimeout(t));
function send() {
    e('query', { audience: audience.value, search: search.value || null, page: o.value.page, perPage: o.value.rowsPerPage });
}
function changeAudience(v: 'pegawai' | 'mahasiswa') {
    audience.value = v;
    o.value.page = 1;
    send();
}
function searchChange() {
    clearTimeout(t);
    t = setTimeout(() => {
        o.value.page = 1;
        send();
    }, 300);
}
</script>
<template>
    <section class="space-y-3">
        <div class="flex gap-2">
            <Button :variant="audience === 'pegawai' ? 'default' : 'outline'"
                @click="changeAudience('pegawai')">Pegawai</Button><Button
                :variant="audience === 'mahasiswa' ? 'default' : 'outline'"
                @click="changeAudience('mahasiswa')">Mahasiswa</Button>
        </div>
        <p class="text-xs font-semibold tracking-wide uppercase">Cari &amp; Filter</p>
        <div class="relative">
            <Search class="text-muted-foreground absolute top-3 left-3 size-4" /><Input v-model="search" class="pl-9"
                placeholder="Cari nama atau email" @input="searchChange" />
        </div>
        <DataTable v-model:server-options="o" :headers="[
            { text: 'Nama', value: 'nama' },
            { text: 'NIP/NIM', value: 'nim' },
            { text: 'Email', value: 'email' },
            { text: 'Jabatan', value: 'role' },
            { text: 'Aksi', value: 'id', width: 180, sortable: false },
        ]" :items="users.data" :loading="loading" :server-items-length="users.total" items-label="pengguna">
            <template #item-role="{ role }">{{ role?.nama || '—' }}</template><template #item-id="{ id, ...raw }">
                <div class="flex gap-2">
                    <Button variant="outline"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        size="icon" @click="$emit('view', { id, ...raw })">
                        <Eye />
                    </Button>
                    <Button variant="outline"
                        class="size-8 rounded-lg border-orange-200 bg-white text-orange-600 hover:bg-orange-600 hover:text-white"
                        size="icon" @click="$emit('edit', { id, ...raw })">
                        <Pencil />
                    </Button><Button v-if="canReset"
                        class="size-8 rounded-lg border-blue-200 bg-white text-blue-600 hover:bg-blue-600 hover:text-white"
                        variant="outline" size="icon" @click="$emit('reset', { id, ...raw })">
                        <KeyRound />
                    </Button><Button variant="outline"
                        class="size-8 rounded-lg border-red-200 bg-white text-red-600 hover:bg-red-600 hover:text-white"
                        size="icon" @click="$emit('delete', { id, ...raw })">
                        <Trash2 />
                    </Button>
                </div>
            </template>
        </DataTable>
    </section>
</template>
