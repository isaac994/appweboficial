<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { User as UserIcon, Mail, Phone, MapPin, CreditCard, Save, Check } from 'lucide-vue-next';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configuración de Perfil',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;

const form = useForm({
    name: user.name || '',
    apellidos: user.apellidos || '',
    telefono: user.telefono || '',
    ci: user.ci || '',
    direccion: user.direccion || '',
});

const showSuccess = ref(false);

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess.value = true;
            setTimeout(() => {
                showSuccess.value = false;
            }, 3000);
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Configuración de Perfil" />

        <SettingsLayout>
            <div class="max-w-4xl mx-auto space-y-8">
                <!-- Header con información del usuario -->
                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 rounded-2xl p-8 text-white">
                    <div class="flex items-center space-x-4">
                        <div class="h-20 w-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                            <UserIcon class="h-10 w-10 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">{{ user.name }} {{ user.apellidos || '' }}</h1>
                            <p class="text-blue-100">{{ user.email }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-sm">
                                <span v-if="user.telefono" class="flex items-center">
                                    <Phone class="h-4 w-4 mr-1" />
                                    {{ user.telefono }}
                                </span>
                                <span v-if="user.ci" class="flex items-center">
                                    <CreditCard class="h-4 w-4 mr-1" />
                                    CI: {{ user.ci }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de perfil -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-xl border border-blue-500/20 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600/20 to-cyan-500/20 px-8 py-6 border-b border-blue-500/30">
                        <h2 class="text-xl font-semibold text-white">Información Personal</h2>
                        <p class="text-blue-100 mt-1">Actualiza tu información personal y de contacto</p>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-8">
                        <!-- Información básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="name" class="text-sm font-medium text-blue-200 flex items-center">
                                    <UserIcon class="h-4 w-4 mr-2 text-cyan-400" />
                                    Nombre *
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    required
                                    autocomplete="given-name"
                                    placeholder="Tu nombre"
                                    class="bg-slate-700/50 border-slate-600 text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-cyan-400/20"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="apellidos" class="text-sm font-medium text-blue-200 flex items-center">
                                    <UserIcon class="h-4 w-4 mr-2 text-cyan-400" />
                                    Apellidos
                                </Label>
                                <Input
                                    id="apellidos"
                                    v-model="form.apellidos"
                                    autocomplete="family-name"
                                    placeholder="Tus apellidos"
                                    class="bg-slate-700/50 border-slate-600 text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-cyan-400/20"
                                />
                                <InputError :message="form.errors.apellidos" />
                            </div>
                        </div>

                        <!-- Información de contacto -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="email" class="text-sm font-medium text-blue-200 flex items-center">
                                    <Mail class="h-4 w-4 mr-2 text-cyan-400" />
                                    Email *
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    :value="user.email"
                                    disabled
                                    readonly
                                    autocomplete="email"
                                    placeholder="tu@email.com"
                                    class="bg-slate-800/50 border-slate-600 text-gray-400 cursor-not-allowed"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="telefono" class="text-sm font-medium text-blue-200 flex items-center">
                                    <Phone class="h-4 w-4 mr-2 text-cyan-400" />
                                    Teléfono
                                </Label>
                                <Input
                                    id="telefono"
                                    type="tel"
                                    v-model="form.telefono"
                                    autocomplete="tel"
                                    placeholder="+591 7XXX XXXX"
                                    class="bg-slate-700/50 border-slate-600 text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-cyan-400/20"
                                />
                                <InputError :message="form.errors.telefono" />
                            </div>
                        </div>

                        <!-- Información adicional -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="ci" class="text-sm font-medium text-blue-200 flex items-center">
                                    <CreditCard class="h-4 w-4 mr-2 text-cyan-400" />
                                    Cédula de Identidad
                                </Label>
                                <Input
                                    id="ci"
                                    v-model="form.ci"
                                    placeholder="12345678"
                                    class="bg-slate-700/50 border-slate-600 text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-cyan-400/20"
                                />
                                <InputError :message="form.errors.ci" />
                            </div>

                            <div class="space-y-2">
                                <Label for="direccion" class="text-sm font-medium text-blue-200 flex items-center">
                                    <MapPin class="h-4 w-4 mr-2 text-cyan-400" />
                                    Dirección
                                </Label>
                                <Textarea
                                    id="direccion"
                                    v-model="form.direccion"
                                    rows="3"
                                    placeholder="Tu dirección completa"
                                    class="bg-slate-700/50 border-slate-600 text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-cyan-400/20"
                                />
                                <InputError :message="form.errors.direccion" />
                            </div>
                        </div>

                        <!-- Verificación de email -->
                        <div v-if="mustVerifyEmail && !user.email_verified_at" class="bg-yellow-500/20 border border-yellow-500/30 rounded-lg p-4">
                            <p class="text-sm text-yellow-200">
                                Tu dirección de email no está verificada.
                                <Link
                                    :href="route('verification.send')"
                                    method="post"
                                    as="button"
                                    class="text-yellow-300 underline hover:text-yellow-100 font-medium"
                                >
                                    Haz clic aquí para reenviar el email de verificación.
                                </Link>
                            </p>
                            <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-400">
                                Se ha enviado un nuevo enlace de verificación a tu dirección de email.
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-center justify-between pt-6 border-t border-slate-600">
                            <div class="flex items-center space-x-4">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-8 py-2 rounded-lg flex items-center space-x-2 shadow-lg"
                                >
                                    <Save class="h-4 w-4" />
                                    <span>{{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}</span>
                                </Button>

                                <Transition
                                    enter-active-class="transition ease-in-out duration-300"
                                    enter-from-class="opacity-0 scale-95"
                                    enter-to-class="opacity-100 scale-100"
                                    leave-active-class="transition ease-in-out duration-300"
                                    leave-from-class="opacity-100 scale-100"
                                    leave-to-class="opacity-0 scale-95"
                                >
                                    <div v-show="showSuccess || form.recentlySuccessful" class="flex items-center space-x-2 text-green-600">
                                        <Check class="h-4 w-4" />
                                        <span class="text-sm font-medium">¡Guardado exitosamente!</span>
                                    </div>
                                </Transition>
                            </div>

                            <Link
                                :href="route('dashboard')"
                                class="text-gray-500 hover:text-gray-700 text-sm font-medium"
                            >
                                Cancelar
                            </Link>
                        </div>
                    </form>
                </div>

            </div>
        </SettingsLayout>
    </AppLayout>
</template>
