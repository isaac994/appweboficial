<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Proveedores</h1>
            <p class="text-purple-300">Administra los proveedores del sistema</p>
          </div>
          <Link
            :href="route('proveedores.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Proveedor
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="flex gap-4">
            <div class="flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar proveedores..."
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @input="debounceSearch"
              />
            </div>
            <button
              @click="clearFilters"
              class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
            >
              Limpiar Filtros
            </button>
          </div>
        </div>

        <!-- Proveedores Table -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-purple-500/30">
              <thead class="bg-black/30">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Nombre
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Teléfono
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Correo
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Dirección
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Productos
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-medium text-purple-300 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-black/20 divide-y divide-purple-500/30">
                <tr v-for="proveedor in proveedores.data" :key="proveedor.id_proveedor" class="hover:bg-black/30 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-white">{{ proveedor.nombre }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-purple-300">{{ proveedor.telefono || 'No especificado' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-purple-300">{{ proveedor.correo || 'No especificado' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-purple-300">{{ proveedor.direccion || 'No especificada' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                      {{ proveedor.productos_count }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                      <Link
                        :href="route('proveedores.show', proveedor.id_proveedor)"
                        class="text-purple-400 hover:text-purple-300 transition-colors duration-200"
                      >
                        Ver
                      </Link>
                      <Link
                        :href="route('proveedores.edit', proveedor.id_proveedor)"
                        class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                      >
                        Editar
                      </Link>
                      <button
                        @click="deleteProveedor(proveedor)"
                        class="text-red-400 hover:text-red-300 transition-colors duration-200"
                      >
                        Eliminar
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="proveedores.links && proveedores.links.length > 3" class="mt-8 flex justify-center">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-4">
            <div class="flex space-x-1">
              <Link
                v-for="link in proveedores.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                  !link.url || link.url === '#'
                    ? 'text-gray-500 cursor-not-allowed'
                    : link.active
                    ? 'bg-purple-600 text-white'
                    : 'text-purple-300 hover:text-white hover:bg-purple-600/50'
                ]"
                v-html="link.label"
                @click="link.url && link.url !== '#' ? null : $event.preventDefault()"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-black/90 border border-purple-500/30 rounded-xl p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-white mb-4">Confirmar Eliminación</h3>
          <p class="text-purple-300 mb-6">
            ¿Estás seguro de que quieres eliminar el proveedor "{{ proveedorToDelete?.nombre }}"? Esta acción no se puede deshacer.
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
              class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
            >
              Cancelar
            </button>
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

interface Proveedor {
  id_proveedor: number
  nombre: string
  telefono: string | null
  direccion: string | null
  correo: string | null
  productos_count: number
}

interface PaginatedData {
  data: Proveedor[]
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
  proveedores: PaginatedData
  filters: {
    search?: string
  }
}>()

const search = ref(props.filters.search || '')
const showDeleteModal = ref(false)
const proveedorToDelete = ref<Proveedor | null>(null)

const debounceSearch = () => {
  router.get(route('proveedores.index'), {
    search: search.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  router.get(route('proveedores.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const deleteProveedor = (proveedor: Proveedor) => {
  proveedorToDelete.value = proveedor
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (proveedorToDelete.value) {
    router.delete(route('proveedores.destroy', proveedorToDelete.value.id_proveedor), {
      onSuccess: () => {
        showDeleteModal.value = false
        proveedorToDelete.value = null
      }
    })
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
