<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, Head, router } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { onMounted, ref } from 'vue'

const form = useForm({
    nama: '',
    email: '',
    password: '',
})

function submit() {
    form.post(route('users.store'))
}

const show = ref(false)
onMounted(() => {
  setTimeout(() => {
    show.value = true
  }, 50)
})

</script>

<template>
    <AppLayout>
        <Transition name="slide-up" mode="out-in">
            <div v-if="show" class="max-w-lg mx-auto space-y-6 p-6 bg-white rounded shadow">
                <h2 class="text-xl font-semibold">Tambah User</h2>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <Label for="nama">Nama</Label>
                        <Input v-model="form.nama" id="nama" type="text" placeholder="Nama lengkap" />
                        <InputError :message="form.errors.nama" />
                    </div>

                    <div>
                        <Label for="email">Email</Label>
                        <Input v-model="form.email" id="email" type="email" placeholder="user@example.com" />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div>
                        <Label for="password">Password</Label>
                        <Input v-model="form.password" id="password" type="password" placeholder="••••••••" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <Button type="submit" :disabled="form.processing">
                        Simpan
                    </Button>
                </form>
            </div>
        </Transition>
    </AppLayout>
</template>
