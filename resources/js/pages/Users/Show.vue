<template>
    <AppLayout title="Detalles del Usuario">
        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Detalles del Usuario</h2>
                                <p class="text-gray-600 mt-1">Información completa del usuario</p>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('users.edit', user.id)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    Editar
                                </Link>
                                <Link
                                    :href="route('users.index')"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    Volver
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del usuario -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Avatar y info básica -->
                            <div class="lg:col-span-1">
                                <div class="text-center">
                                    <div class="mx-auto h-32 w-32 rounded-full bg-blue-500 flex items-center justify-center mb-4">
                                        <span class="text-white font-bold text-4xl">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ user.name }}</h3>
                                    <p class="text-gray-600">{{ user.email }}</p>

                                    <!-- Estado -->
                                    <div class="mt-4">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                            :class="user.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ user.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detalles -->
                            <div class="lg:col-span-2">
                                <div class="space-y-6">
                                    <!-- Información personal -->
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h4>
                                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ user.name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ user.email }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                                <dd class="mt-1">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                        :class="user.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                    >
                                                        {{ user.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Roles -->
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900 mb-4">Roles Asignados</h4>
                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role.id"
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                                :class="role.name === 'Administrador' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Permisos -->
                                    <div>
                                        <h4 class="text-lg font-medium text-gray-900 mb-4">Permisos</h4>
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                <div
                                                    v-for="permission in user.permissions"
                                                    :key="permission"
                                                    class="text-sm text-gray-700 bg-white px-2 py-1 rounded border"
                                                >
                                                    {{ permission }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Acciones</h4>
                        <div class="flex gap-4">
                            <button
                                @click="toggleStatus"
                                class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg transition-colors"
                                :disabled="user.id === $page.props.auth.user.id"
                            >
                                {{ user.estado === 'activo' ? 'Desactivar Usuario' : 'Activar Usuario' }}
                            </button>
                            <Link
                                :href="route('users.edit', user.id)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                Editar Usuario
                            </Link>
                        </div>
                        <p v-if="user.id === $page.props.auth.user.id" class="text-sm text-gray-500 mt-2">
                            No puedes desactivar tu propia cuenta
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps({
    user: Object
})

const toggleStatus = () => {
    if (confirm(`¿Estás seguro de que quieres ${props.user.estado === 'activo' ? 'desactivar' : 'activar'} este usuario?`)) {
        router.post(route('users.toggle-status', props.user.id), {}, {
            preserveState: true
        })
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
