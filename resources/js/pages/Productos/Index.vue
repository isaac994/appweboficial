<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Productos</h1>
            <p class="text-purple-300">Administra el inventario de productos</p>
          </div>
          <Link
            :href="route('productos.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Producto
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <input
                v-model="search"
                type="text"
                placeholder="Buscar productos..."
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @input="debounceSearch"
              />
            </div>
            <div>
              <select
                v-model="selectedCategoria"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @change="applyFilters"
              >
                <option value="">Todas las categorías</option>
                <option v-for="categoria in categorias" :key="categoria.id_categoria" :value="categoria.id_categoria">
                  {{ categoria.nombre }}
                </option>
              </select>
            </div>
            <div>
              <select
                v-model="selectedMarca"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @change="applyFilters"
              >
                <option value="">Todas las marcas</option>
                <option v-for="marca in marcas" :key="marca.id_marca" :value="marca.id_marca">
                  {{ marca.nombre }}
                </option>
              </select>
            </div>
            <div>
              <button
                @click="clearFilters"
                class="w-full px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
              >
                Limpiar Filtros
              </button>
            </div>
          </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="producto in productos.data" :key="producto.id_producto" class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
            <!-- Product Image -->
            <div class="aspect-square bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center">
              <img
                v-if="producto.img_url"
                :src="producto.img_url + '?v=' + Date.now()"
                :alt="producto.nombre"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
              <div v-else class="w-full h-full bg-gradient-to-br from-purple-500/30 to-pink-500/30 flex items-center justify-center">
                <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
              <h3 class="text-lg font-semibold text-white mb-2">{{ producto.nombre }}</h3>
              <p class="text-purple-300 text-sm mb-3 line-clamp-2">{{ producto.descripcion }}</p>

              <div class="space-y-2 mb-4">
                <div class="flex justify-between">
                  <span class="text-gray-400 text-sm">Categoría:</span>
                  <span class="text-white text-sm">{{ producto.categoria?.nombre || 'Sin categoría' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400 text-sm">Marca:</span>
                  <span class="text-white text-sm">{{ producto.marca?.nombre || 'Sin marca' }}</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                  <span class="text-gray-400 text-sm">Precio Compra:</span>
                  <span class="text-green-400 font-semibold">Bs {{ producto.precio_compra }}</span>
                </div>
                <div class="flex justify-between items-center mb-4">
                  <span class="text-gray-400 text-sm">Precio Venta:</span>
                  <span class="text-purple-400 font-semibold">Bs {{ producto.precio_venta }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex space-x-2">
                <Link
                  :href="route('productos.show', producto.id_producto)"
                  class="flex-1 px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 text-center"
                >
                  Ver
                </Link>
                <Link
                  :href="route('productos.edit', producto.id_producto)"
                  class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 text-center"
                >
                  Editar
                </Link>
                <button
                  @click="deleteProducto(producto)"
                  class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                >
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="productos.links && productos.links.length > 3" class="mt-8 flex justify-center">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-4">
            <div class="flex space-x-1">
              <Link
                v-for="link in productos.links"
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
            ¿Estás seguro de que quieres eliminar el producto "{{ productoToDelete?.nombre }}"? Esta acción no se puede deshacer.
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

interface Producto {
  id_producto: number
  nombre: string
  descripcion?: string
  precio_compra: number
  precio_venta: number
  img_url?: string
  categoria?: {
    nombre: string
  }
  marca?: {
    nombre: string
  }
  proveedor?: {
    nombre: string
  }
}

interface Categoria {
  id_categoria: number
  nombre: string
}

interface Marca {
  id_marca: number
  nombre: string
}

interface PaginatedData {
  data: Producto[]
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
  productos: PaginatedData
  categorias: Categoria[]
  marcas: Marca[]
  filters: {
    search?: string
    categoria?: string
    marca?: string
  }
}>()

const search = ref(props.filters.search || '')
const selectedCategoria = ref(props.filters.categoria || '')
const selectedMarca = ref(props.filters.marca || '')
const showDeleteModal = ref(false)
const productoToDelete = ref<Producto | null>(null)

const debounceSearch = () => {
  router.get(route('productos.index'), {
    search: search.value,
    categoria: selectedCategoria.value,
    marca: selectedMarca.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const applyFilters = () => {
  router.get(route('productos.index'), {
    search: search.value,
    categoria: selectedCategoria.value,
    marca: selectedMarca.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  selectedCategoria.value = ''
  selectedMarca.value = ''
  router.get(route('productos.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const deleteProducto = (producto: Producto) => {
  productoToDelete.value = producto
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (productoToDelete.value) {
    router.delete(route('productos.destroy', productoToDelete.value.id_producto), {
      onSuccess: () => {
        showDeleteModal.value = false
        productoToDelete.value = null
      }
    })
  }
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-purple-500/30', 'to-pink-500/30')
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
