<template>
  <AppSidebarLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Clientes</h1>
            <p class="text-blue-300">Administra tu base de datos de clientes</p>
          </div>
          <Link
            :href="route('clientes.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Cliente
          </Link>
        </div>

        <!-- Estadísticas Rápidas -->


        <!-- Filtros -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 mb-8">

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Búsqueda -->
            <div class="md:col-span-2">
              <label for="search" class="block text-sm font-medium text-blue-300 mb-2">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Búsqueda Multicampo
              </label>
              <div class="relative">
                <input
                  id="search"
                  v-model="filters.search"
                  @input="debounceSearch"
                  type="text"
                  placeholder="Buscar........."
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                  <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <div v-if="filters.search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button
                    @click="clearSearch"
                    class="text-blue-300 hover:text-white transition-colors duration-200"
                    title="Limpiar búsqueda"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              </div>
              <p class="text-xs text-blue-400 mt-1">
                Busca en: nombre, apellidos, CI/NIT y teléfono simultáneamente
              </p>
            </div>

            <!-- Ordenar por -->

          </div>
        </div>

        <!-- Resultados de búsqueda -->
        <div v-if="filters.search" class="mb-4">
          <div class="bg-blue-500/20 border border-blue-500/30 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-300 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="text-blue-300">
                  Resultados para: <span class="text-white font-medium">"{{ filters.search }}"</span>
                </span>
              </div>
              <span class="text-blue-300">
                {{ clientes.total }} cliente{{ clientes.total !== 1 ? 's' : '' }} encontrado{{ clientes.total !== 1 ? 's' : '' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Tabla de Clientes -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
          <div class="px-6 py-4 border-b border-blue-500/30">
            <h3 class="text-xl font-semibold text-white">Lista de Clientes</h3>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-black/30">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                    Cliente
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                    CI/NIT
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                    Teléfono
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                    Estadísticas
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-blue-500/30">
                <tr v-for="cliente in clientes.data" :key="cliente.id_cliente" class="hover:bg-black/10 transition-colors duration-200">
                  <!-- Cliente -->
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                          <span class="text-sm font-semibold text-white">{{ getInitials(cliente.nombre) }}</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-white">{{ cliente.nombre }} {{ cliente.apellidos || '' }}</div>
                        <div class="text-sm text-blue-300">ID: #{{ cliente.id_cliente }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- CI/NIT -->
                  <td class="px-6 py-4">
                    <div class="text-sm text-blue-300">
                      {{ cliente.ci || 'No especificado' }}
                    </div>
                  </td>

                  <!-- Teléfono -->
                  <td class="px-6 py-4">
                    <div class="text-sm text-blue-300">
                      {{ cliente.telefono || 'No especificado' }}
                    </div>
                  </td>

                  <!-- Estadísticas -->
                  <td class="px-6 py-4">
                    <div class="space-y-1">
                      <div class="text-sm text-white">
                        <span class="font-medium">{{ cliente.ventas_count || 0 }}</span> ventas
                      </div>
                      <div class="text-sm text-blue-300">
                        Total: {{ formatCurrency(cliente.total_compras || 0) }}
                      </div>
                      <div class="text-sm text-blue-300">
                        Desde: {{ formatDate(cliente.created_at) }}
                      </div>
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
                        class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-600 hover:to-cyan-700 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105"
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
          <div v-if="clientes.links && clientes.links.length > 3" class="px-6 py-4 border-t border-blue-500/30">
            <div class="flex items-center justify-between">
                      <div class="text-sm text-blue-300">
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
                      ? 'bg-blue-600 text-white'
                      : 'text-blue-300 hover:bg-black/30'
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
  apellidos?: string
  ci?: string
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

let debounceTimer: NodeJS.Timeout | null = null

const debounceSearch = () => {
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  debounceTimer = setTimeout(() => {
    applyFilters()
  }, 300) // Debounce de 300ms
}

const clearSearch = () => {
  filters.value.search = ''
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
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    currencyDisplay: 'symbol'
  }).format(amount).replace('BOB', 'Bs')
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
