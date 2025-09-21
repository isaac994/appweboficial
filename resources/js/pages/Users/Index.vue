<template>
    <AppLayout title="Gestión de Usuarios">
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Gestión de Usuarios</h2>
                                <p class="text-gray-600 mt-1">Administra los usuarios del sistema</p>
                            </div>
                            <Link
                                :href="route('users.create')"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
                            >
                                <Plus class="w-4 h-4" />
                                Nuevo Usuario
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Búsqueda -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Nombre o email..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @input="search"
                                />
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <select
                                    v-model="filters.estado"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @change="search"
                                >
                                    <option value="">Todos</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>

                            <!-- Rol -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                                <select
                                    v-model="filters.rol"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    @change="search"
                                >
                                    <option value="">Todos</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.name">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Limpiar filtros -->
                            <div class="flex items-end">
                                <button
                                    @click="clearFilters"
                                    class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    Limpiar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de usuarios -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Roles
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Creación
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
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
                                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ user.email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="role in user.roles"
                                                :key="role.id"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                :class="role.name === 'Administrador' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="user.estado === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ user.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(user.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="route('users.show', user.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Ver
                                            </Link>
                                            <Link
                                                :href="route('users.edit', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                @click="toggleStatus(user)"
                                                class="text-orange-600 hover:text-orange-900"
                                                :disabled="user.id === $page.props.auth.user.id"
                                            >
                                                {{ user.estado === 'activo' ? 'Desactivar' : 'Activar' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
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
                                        <Link
                                            v-for="link in users.links"
                                            :key="link.label"
                                            :href="link.url"
                                            v-html="link.label"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                            :class="link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                        />
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
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Plus } from 'lucide-vue-next'

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object
})

const filters = ref({
    search: props.filters.search || '',
    estado: props.filters.estado || '',
    rol: props.filters.rol || ''
})

const search = () => {
    router.get(route('users.index'), filters.value, {
        preserveState: true,
        replace: true
    })
}

const clearFilters = () => {
    filters.value = {
        search: '',
        estado: '',
        rol: ''
    }
    search()
}

const toggleStatus = (user) => {
    if (confirm(`¿Estás seguro de que quieres ${user.estado === 'activo' ? 'desactivar' : 'activar'} este usuario?`)) {
        router.post(route('users.toggle-status', user.id), {}, {
            preserveState: true
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
