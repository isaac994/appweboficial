<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Marcas</h1>
            <p class="text-purple-300">Administra las marcas de productos</p>
          </div>
          <Link
            :href="route('marcas.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nueva Marca
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="flex space-x-4">
            <div class="flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar marcas..."
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @input="debounceSearch"
              />
            </div>
            <button
              @click="clearFilters"
              class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
            >
              Limpiar
            </button>
          </div>
        </div>

        <!-- Brands Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="marca in marcas.data" :key="marca.id_marca" class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-white">{{ marca.nombre }}</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                  {{ marca.productos_count }} productos
                </span>
              </div>

              <div v-if="marca.pais_origen" class="mb-4">
                <span class="text-gray-400 text-sm">País de origen:</span>
                <span class="text-white text-sm ml-2">{{ marca.pais_origen }}</span>
              </div>

              <!-- Actions -->
              <div class="flex space-x-2">
                <Link
                  :href="route('marcas.show', marca.id_marca)"
                  class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 text-center"
                >
                  Ver
                </Link>
                <Link
                  :href="route('marcas.edit', marca.id_marca)"
                  class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 text-center"
                >
                  Editar
                </Link>
                <button
                  @click="deleteMarca(marca)"
                  class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="marcas.links && marcas.links.length > 3" class="mt-8 flex justify-center">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-4">
            <div class="flex space-x-1">
              <Link
                v-for="link in marcas.links"
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
            ¿Estás seguro de que quieres eliminar la marca "{{ marcaToDelete?.nombre }}"? Esta acción no se puede deshacer.
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

interface Marca {
  id_marca: number
  nombre: string
  pais_origen?: string
  productos_count: number
}

interface PaginatedData {
  data: Marca[]
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
  marcas: PaginatedData
  filters: {
    search?: string
  }
}>()

const search = ref(props.filters.search || '')
const showDeleteModal = ref(false)
const marcaToDelete = ref<Marca | null>(null)

const debounceSearch = () => {
  router.get(route('marcas.index'), {
    search: search.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  router.get(route('marcas.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const deleteMarca = (marca: Marca) => {
  marcaToDelete.value = marca
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (marcaToDelete.value) {
    router.delete(route('marcas.destroy', marcaToDelete.value.id_marca), {
      onSuccess: () => {
        showDeleteModal.value = false
        marcaToDelete.value = null
      }
    })
  }
}
</script>
