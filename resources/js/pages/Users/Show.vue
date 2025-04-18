<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { useForm, Head, router } from '@inertiajs/vue3'
import { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengguna',
        href: '/users',
    },
    {
        title: 'Detail',
        href: '/users',
    },
]

const props = defineProps<{
    user: {
        id: number
        nama: string
        email: string
        role: { nama: string } | null
    }
}>()

const show = ref(false)

onMounted(() => {
    setTimeout(() => {
        show.value = true
    }, 50)
})

function toBack() {
    router.visit(route('users.index'))
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="p-4 space-y-4">
                <h2 class="text-xl font-semibold">Detail User</h2>
                <table class="w-full table-auto border border-gray-300 mb-0">
                    <tbody class="text-sm">
                        <tr class="border-b">
                            <td class="px-4 py-4 font-medium text-gray-600 w-1/3 border border-gray-200">Nama</td>
                            <td class="px-4 py-4 border border-gray-200">{{ user.nama }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-4 font-medium text-gray-600 border border-gray-200">Email</td>
                            <td class="px-4 py-4 border border-gray-200">{{ user.email }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="px-4 py-4 font-medium text-gray-600 border border-gray-200">Role</td>
                            <td class="px-4 py-4 border border-gray-200">{{ user.role?.nama ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="w-full flex justify-end">
                    <Button type="button" @click="toBack"
                        class="mt-4 border border-orange-400 text-orange-500 hover:bg-orange-100 bg-white-500">
                        <span>Kembali</span>
                    </Button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>
