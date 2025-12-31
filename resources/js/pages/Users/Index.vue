<template>
    <AppLayout title="Gestión de Usuarios">
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Usuarios</h1>
                            <p class="text-blue-300">Administra los usuarios del sistema</p>
                        </div>
                        <Link
                            :href="route('users.create')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                        >
                            <Plus class="w-5 h-5 mr-2" />
                            Nuevo Usuario
                        </Link>
                    </div>
                </div>

                <!-- Success Messages -->
                <div v-if="$page.props.flash && $page.props.flash.success" class="mb-8">
                    <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-green-400 font-semibold">{{ $page.props.flash.success }}</p>
                        </div>
                    </div>
                </div>

                <!-- Error Messages -->
                <div v-if="$page.props.flash && $page.props.flash.error" class="mb-8">
                    <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-red-400 font-semibold">{{ $page.props.flash.error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda Multicampo -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 mb-8">
                    <div class="flex flex-col space-y-4">
                        <!-- Búsqueda Potente -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">
                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Búsqueda Avanzada
                            </label>
                            <div class="relative">
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Buscar por nombre, apellido, teléfono, CI, estado o rol..."
                                    class="w-full px-4 py-3 pl-12 bg-black/30 border border-blue-500/50 rounded-xl text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                                    @input="search"
                                />
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <div v-if="filters.search" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                    <button
                                        @click="clearSearch"
                                        class="text-gray-400 hover:text-white transition-colors"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-blue-300">
                                💡 Puedes buscar por: nombre, apellido, teléfono, CI, estado (activo/inactivo) o rol
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tabla de usuarios -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-500/20">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                                        Roles
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                                        Fecha Creación
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr v-if="users.data && users.data.length > 0" v-for="user in users.data" :key="user.id" class="hover:bg-blue-500/10 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                                    <span class="text-white font-medium text-sm">
                                                        {{ user.name.charAt(0).toUpperCase() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-white">
                                                    {{ user.name }} {{ user.apellidos || '' }}
                                                </div>
                                                <div v-if="user.telefono" class="text-xs text-gray-400">
                                                    📞 {{ user.telefono }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role.id"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="role.name === 'Propietario' ? 'bg-red-500/20 text-red-400' : 'bg-green-500/20 text-green-400'"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="user.estado === 'activo' ? 'bg-green-500/20 text-green-400 border border-green-500/50' : 'bg-red-500/20 text-red-400 border border-red-500/50'"
                                        >
                                            {{ user.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="route('users.show', user.id)"
                                                class="text-blue-400 hover:text-blue-300"
                                            >
                                                Ver
                                            </Link>
                                            <Link
                                                :href="route('users.edit', user.id)"
                                                class="text-cyan-400 hover:text-cyan-300"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                @click="toggleStatus(user)"
                                                class="text-orange-400 hover:text-orange-300"
                                                :class="{ 'opacity-50 cursor-not-allowed': user.id === page.props.auth.user.id }"
                                            >
                                                {{ user.estado === 'activo' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-else>
                                    <td colspan="6" class="px-6 py-12 text-center text-blue-300">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                            </svg>
                                            <h3 class="text-lg font-medium text-white mb-2">No hay usuarios</h3>
                                            <p class="text-blue-300 mb-4">No se encontraron usuarios con los filtros aplicados.</p>
                                            <Link
                                                :href="route('users.create')"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                                            >
                                                Crear primer usuario
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="users.data && users.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link
                                    v-if="users.prev_page_url"
                                    :href="users.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Anterior
                                </Link>
                                <Link
                                    v-if="users.next_page_url"
                                    :href="users.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Siguiente
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{ users.from }}</span>
                                        a
                                        <span class="font-medium">{{ users.to }}</span>
                                        de
                                        <span class="font-medium">{{ users.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <template v-for="link in users.links" :key="link.label">
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                v-html="link.label"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                                :class="link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                            />
                                            <span
                                                v-else
                                                v-html="link.label"
                                                class="relative inline-flex items-center px-4 py-2 border text-sm font-medium bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed"
                                            />
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Plus } from 'lucide-vue-next'
import Swal from 'sweetalert2'

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object
})

const page = usePage()

const filters = ref({
    search: props.filters.search || ''
})

const search = () => {
    router.get(route('users.index'), filters.value, {
        preserveState: true,
        replace: true
    })
}

const clearSearch = () => {
    filters.value.search = ''
    search()
}

const toggleStatus = (user) => {
    // Prevenir que un usuario se desactive a sí mismo
    if (user.id === page.props.auth.user.id && user.estado === 'activo') {
        Swal.fire({
            title: 'Error',
            text: 'No puedes desactivarte a ti mismo.',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Entendido'
        })
        return
    }

    if (confirm(`¿Estás seguro de que quieres ${user.estado === 'activo' ? 'desactivar' : 'activar'} este usuario?`)) {
        router.post(route('users.toggle-status', user.id), {}, {
            preserveState: true,
            onError: (errors) => {
                if (errors.error) {
                    Swal.fire({
                        title: 'Error',
                        text: errors.error,
                        icon: 'error',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Entendido'
                    })
                }
            }
        })
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

// Debounce para la búsqueda
let searchTimeout
watch(() => filters.value.search, (newValue) => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        search()
    }, 500)
})
</script>
