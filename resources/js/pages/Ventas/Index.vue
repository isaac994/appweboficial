<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Ventas</h1>
            <p class="text-purple-300">Administra las ventas y pedidos de tus clientes</p>
          </div>
          <button
            @click="router.visit(route('ventas.create'))"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nueva Venta
          </button>
        </div>

        <!-- Filters -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por cliente..."
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @input="debounceSearch"
              />
            </div>
            <div class="flex gap-2">
              <button
                @click="clearFilters"
                class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
              >
                Limpiar
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-purple-900/50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">Venta</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">Cliente</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">Productos</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">Total</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">Fecha</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-purple-500/20">
                <tr v-for="venta in ventas.data" :key="venta.id_venta" class="hover:bg-purple-900/20 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-white">#{{ venta.id_venta }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-white">{{ venta.cliente?.nombre }}</div>
                    <div class="text-sm text-purple-300">{{ venta.cliente?.correo_electronico }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-purple-300">
                      <div v-for="detalle in venta.detalles?.slice(0, 2)" :key="detalle.id_detalle">
                        {{ detalle.producto?.nombre }} x{{ detalle.cantidad }}
                      </div>
                      <div v-if="venta.detalles && venta.detalles.length > 2" class="text-xs text-gray-500">
                        +{{ venta.detalles.length - 2 }} más...
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-lg font-bold text-white">${{ venta.total }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-purple-300">{{ formatDate(venta.created_at) }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button
                        @click="router.visit(route('ventas.show', venta.id_venta))"
                        class="text-purple-400 hover:text-purple-300 transition-colors duration-200"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </button>
                      <button
                        @click="router.visit(route('ventas.edit', venta.id_venta))"
                        class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                      <button
                        @click="deleteVenta(venta)"
                        class="text-red-400 hover:text-red-300 transition-colors duration-200"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="bg-purple-900/50 px-6 py-3 border-t border-purple-500/20">
            <div class="flex items-center justify-between">
              <div class="text-sm text-purple-300">
                Mostrando {{ ventas.from }} a {{ ventas.to }} de {{ ventas.total }} resultados
              </div>
              <div class="flex space-x-1">
                <template v-for="link in ventas.links" :key="link.label">
                  <!-- Enlaces habilitados -->
                  <Link
                    v-if="link.url !== null"
                    :href="link.url"
                    :class="[
                      'px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                      link.active
                        ? 'bg-purple-600 text-white'
                        : 'text-purple-300 hover:text-white hover:bg-purple-600/50'
                    ]"
                    v-html="link.label"
                  />
                  <!-- Enlaces deshabilitados -->
                  <span
                    v-else
                    :class="[
                      'px-3 py-2 text-sm font-medium rounded-md text-gray-500 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                  />
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
          <div class="bg-black/90 border border-purple-500/30 rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold text-white mb-4">Confirmar Eliminación</h3>
            <p class="text-purple-300 mb-6">
              ¿Estás seguro de que quieres eliminar la venta #{{ ventaToDelete?.id_venta }}? Esta acción no se puede deshacer.
            </p>
            <div class="flex space-x-3">
              <button
                @click="confirmDelete"
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200"
              >
                Eliminar
              </button>
              <button
                @click="showDeleteModal = false"
                class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200"
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Cliente {
  nombre: string
  correo_electronico?: string
}

interface Producto {
  nombre: string
}

interface DetalleVenta {
  id_detalle: number
  cantidad: number
  producto?: Producto
}

interface Venta {
  id_venta: number
  total: number
  created_at: string
  cliente?: Cliente
  detalles?: DetalleVenta[]
}

interface PaginatedData {
  data: Venta[]
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

const props = defineProps<{
  ventas: PaginatedData
  filters: {
    search?: string
  }
}>()

const search = ref(props.filters.search || '')
const showDeleteModal = ref(false)
const ventaToDelete = ref<Venta | null>(null)

const debounceSearch = () => {
  router.get(route('ventas.index'), { search: search.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  router.get(route('ventas.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const deleteVenta = (venta: Venta) => {
  ventaToDelete.value = venta
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (ventaToDelete.value) {
    router.delete(route('ventas.destroy', ventaToDelete.value.id_venta), {
      onSuccess: () => {
        showDeleteModal.value = false
        ventaToDelete.value = null
      }
    })
  }
}
</script>
