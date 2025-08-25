<template>
  <AppSidebarLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Clientes</h1>
            <p class="text-purple-300">Administra tu base de datos de clientes</p>
          </div>
          <Link
            :href="route('clientes.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Cliente
          </Link>
        </div>

        <!-- Estadísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-purple-300">Total Clientes</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_clientes }}</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-purple-300">Total Ventas</p>
                <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.total_ventas) }}</p>
                <p class="text-sm text-purple-300">{{ stats.total_transacciones || 0 }} transacciones</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-purple-300">Promedio por Cliente</p>
                <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.promedio_ventas) }}</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-purple-300">Clientes Premium</p>
                <p class="text-2xl font-bold text-white">{{ stats.clientes_premium }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filtros -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <h3 class="text-xl font-semibold text-white mb-4">Filtros y Búsqueda</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Búsqueda -->
            <div>
              <label for="search" class="block text-sm font-medium text-purple-300 mb-2">
                Buscar Cliente
              </label>
              <div class="relative">
                <input
                  id="search"
                  v-model="filters.search"
                  type="text"
                  placeholder="Nombre, email o teléfono..."
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Estado de Fidelización -->
            <div>
              <label for="fidelizacion" class="block text-sm font-medium text-purple-300 mb-2">
                Estado de Fidelización
              </label>
              <select
                id="fidelizacion"
                v-model="filters.fidelizacion"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="" class="bg-gray-800 text-white">Todos los niveles</option>
                <option value="Bronce" class="bg-gray-800 text-white">Bronce</option>
                <option value="Plata" class="bg-gray-800 text-white">Plata</option>
                <option value="Oro" class="bg-gray-800 text-white">Oro</option>
                <option value="Premium" class="bg-gray-800 text-white">Premium</option>
              </select>
            </div>

            <!-- Ordenar por -->
            <div>
              <label for="sort" class="block text-sm font-medium text-purple-300 mb-2">
                Ordenar por
              </label>
              <select
                id="sort"
                v-model="filters.sort"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="nombre" class="bg-gray-800 text-white">Nombre</option>
                <option value="created_at" class="bg-gray-800 text-white">Fecha de Registro</option>
                <option value="ventas_count" class="bg-gray-800 text-white">Número de Ventas</option>
                <option value="total_compras" class="bg-gray-800 text-white">Total de Compras</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Tabla de Clientes -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden">
          <div class="px-6 py-4 border-b border-purple-500/30">
            <h3 class="text-xl font-semibold text-white">Lista de Clientes</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-black/30">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Cliente
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Contacto
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Estadísticas
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Estado
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-purple-500/30">
                <tr v-for="cliente in clientes.data" :key="cliente.id_cliente" class="hover:bg-black/10 transition-colors duration-200">
                  <!-- Cliente -->
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center">
                          <span class="text-sm font-semibold text-white">{{ getInitials(cliente.nombre) }}</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-white">{{ cliente.nombre }}</div>
                        <div class="text-sm text-purple-300">ID: #{{ cliente.id_cliente }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Contacto -->
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div v-if="cliente.telefono" class="flex items-center text-sm text-purple-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ cliente.telefono }}
                      </div>
                      <div v-if="cliente.correo_electronico" class="flex items-center text-sm text-purple-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ cliente.correo_electronico }}
                      </div>
                      <div v-if="cliente.direccion" class="flex items-center text-sm text-purple-300">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ cliente.direccion }}
                      </div>
                    </div>
                  </td>

                  <!-- Estadísticas -->
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm text-white">
                        <span class="font-medium">{{ cliente.ventas_count || 0 }}</span> ventas
                      </div>
                      <div class="text-sm text-purple-300">
                        Total: {{ formatCurrency(cliente.total_compras || 0) }}
                      </div>
                      <div class="text-sm text-purple-300">
                        Desde: {{ formatDate(cliente.created_at) }}
                      </div>
                    </div>
                  </td>

                  <!-- Estado -->
                  <td class="px-6 py-4">
                    <div class="flex flex-col space-y-2">
                      <span :class="getFidelizacionClass(cliente.estado_fidelizacion)" class="px-3 py-1 rounded-full text-xs font-medium text-center">
                        {{ cliente.estado_fidelizacion || 'Nuevo' }}
                      </span>
                    </div>
                  </td>

                  <!-- Acciones -->
                  <td class="px-6 py-4">
                    <div class="flex space-x-2">
                      <Link
                        :href="route('clientes.show', cliente.id_cliente)"
                        class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver
                      </Link>
                      <Link
                        :href="route('clientes.edit', cliente.id_cliente)"
                        class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="clientes.links && clientes.links.length > 3" class="px-6 py-4 border-t border-purple-500/30">
            <div class="flex items-center justify-between">
              <div class="text-sm text-purple-300">
                Mostrando {{ clientes.from }} a {{ clientes.to }} de {{ clientes.total }} resultados
              </div>
              <div class="flex space-x-2">
                <Link
                  v-for="link in clientes.links"
                  :key="link.label"
                  :href="link.url || '#'"
                  v-html="link.label"
                  :class="[
                    'px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200',
                    link.url === null
                      ? 'text-gray-400 cursor-not-allowed'
                      : link.active
                      ? 'bg-purple-600 text-white'
                      : 'text-purple-300 hover:bg-black/30'
                  ]"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'

interface Cliente {
  id_cliente: number
  nombre: string
  telefono?: string
  direccion?: string
  correo_electronico?: string
  fecha_nacimiento?: string
  genero?: string
  ventas_count?: number
  total_compras?: number
  estado_fidelizacion?: string
  created_at: string
}

interface PaginatedData {
  data: Cliente[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
}

interface Stats {
  total_clientes?: number
  total_ventas?: number
  promedio_ventas?: number
  clientes_premium?: number
}

const props = defineProps<{
  clientes: PaginatedData
  filters: {
    search?: string
    fidelizacion?: string
    sort?: string
  }
  stats?: Stats
}>()

// Valores por defecto para stats si no se proporcionan
const defaultStats: Stats = {
  total_clientes: 0,
  total_ventas: 0,
  promedio_ventas: 0,
  clientes_premium: 0
}

const search = ref(props.filters.search || '')
const filters = ref({
  search: props.filters.search || '',
  fidelizacion: props.filters.fidelizacion || '',
  sort: props.filters.sort || 'nombre'
})
const showDeleteModal = ref(false)
const clienteToDelete = ref<Cliente | null>(null)

// Usar stats con valores por defecto
const stats = ref(props.stats || defaultStats)

const debounceSearch = () => {
  applyFilters()
}

const applyFilters = () => {
  router.get(route('clientes.index'), {
    search: search.value,
    ...filters.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const deleteCliente = (cliente: Cliente) => {
  clienteToDelete.value = cliente
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (clienteToDelete.value) {
    router.delete(route('clientes.destroy', clienteToDelete.value.id_cliente), {
      onSuccess: () => {
        showDeleteModal.value = false
        clienteToDelete.value = null
      }
    })
  }
}

// Funciones helper
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(amount)
}

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getFidelizacionClass = (estado: string) => {
  const clases: Record<string, string> = {
    'Premium': 'bg-purple-100 text-purple-800',
    'Oro': 'bg-yellow-100 text-yellow-800',
    'Plata': 'bg-gray-100 text-gray-800',
    'Bronce': 'bg-orange-100 text-orange-800'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800'
}
</script>
