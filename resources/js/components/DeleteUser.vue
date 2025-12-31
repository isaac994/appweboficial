<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="space-y-6">
        <div class="mb-8 space-y-0.5">
            <h2 class="text-2xl font-bold text-white tracking-tight">Eliminar Cuenta</h2>
            <p class="text-sm text-gray-300">
                Elimina tu cuenta y todos sus recursos
            </p>
        </div>

        <div class="space-y-4 rounded-lg border border-red-500/30 bg-red-500/10 p-6">
            <div class="relative space-y-2 text-red-200">
                <p class="font-semibold text-lg flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    Advertencia
                </p>
                <p class="text-sm">Por favor, procede con precaución, esta acción no se puede deshacer.</p>
            </div>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg">
                        Eliminar Cuenta
                    </Button>
                </DialogTrigger>
                <DialogContent class="bg-slate-800 border-slate-700">
                    <form class="space-y-6" @submit="deleteUser">
                        <DialogHeader class="space-y-3">
                            <DialogTitle class="text-white text-xl">¿Estás seguro de que quieres eliminar tu cuenta?</DialogTitle>
                            <DialogDescription class="text-gray-300">
                                Una vez que tu cuenta sea eliminada, todos sus recursos y datos también serán eliminados permanentemente.
                                Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="password" class="text-sm font-medium text-blue-200">Contraseña</Label>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                ref="passwordInput"
                                v-model="form.password"
                                placeholder="Ingresa tu contraseña"
                                class="bg-slate-700/50 border-slate-600 text-white placeholder-gray-400 focus:border-red-400 focus:ring-red-400/20"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <DialogFooter class="gap-3">
                            <DialogClose as-child>
                                <Button
                                    variant="secondary"
                                    @click="closeModal"
                                    class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg"
                                >
                                    Cancelar
                                </Button>
                            </DialogClose>

                            <Button
                                type="submit"
                                variant="destructive"
                                :disabled="form.processing"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg"
                            >
                                {{ form.processing ? 'Eliminando...' : 'Eliminar Cuenta' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>
