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

        <!-- Success Messages -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6">
          <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-green-400 font-semibold">{{ $page.props.flash.success }}</p>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="mb-4">
            <h3 class="text-lg font-semibold text-white mb-2">Filtros de Búsqueda</h3>
            <p class="text-purple-300 text-sm">El buscador permite filtrar por nombre, marca, categoría o precio del producto</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre, marca, categoría o precio..."
                class="w-full pl-10 pr-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                @input="debounceSearch"
                title="Busca por nombre del producto, marca, categoría o precio"
              />
            </div>



          </div>
        </div>

        <!-- Products List -->
        <div class="space-y-4">
          <div
            v-for="producto in productos.data"
            :key="producto.id_producto"
            class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:shadow-xl transition-all duration-300"
          >
            <div class="flex items-center space-x-6">
              <!-- Product Image -->
              <div class="w-20 h-20 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                <img
                  v-if="producto.img_url"
                  :src="getImageUrl(producto.img_url)"
                  :alt="producto.nombre"
                  class="w-full h-full object-cover rounded-lg"
                  @error="handleImageError"
                />
                <div v-else class="w-full h-full bg-gradient-to-br from-purple-500/30 to-pink-500/30 flex items-center justify-center rounded-lg">
                  <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              </div>

              <!-- Product Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-1">{{ producto.nombre }}</h3>
                    <p v-if="producto.descripcion" class="text-purple-300 text-sm mb-3">{{ producto.descripcion }}</p>

                    <div class="flex items-center space-x-6 text-sm">
                      <div class="flex items-center space-x-2">
                        <span class="text-gray-400">Categoría:</span>
                        <span class="text-white">{{ producto.categoria?.nombre || 'Sin categoría' }}</span>
                      </div>
                      <div class="flex items-center space-x-2">
                        <span class="text-gray-400">Marca:</span>
                        <span class="text-white">{{ producto.marca?.nombre || 'Sin marca' }}</span>
                      </div>
                      <div class="flex items-center space-x-2">
                        <span class="text-gray-400">Precio:</span>
                        <span class="text-purple-400 font-semibold">Bs {{ producto.precio_venta }}</span>
                      </div>
                      <div class="flex items-center space-x-2">
                        <span class="text-gray-400">Estado:</span>
                        <span class="font-semibold" :class="producto.estado_disponible === 'disponible' ? 'text-green-400' : 'text-red-400'">
                          {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center space-x-2 ml-4">
                    <Link
                      :href="route('productos.show', producto.id_producto)"
                      class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200"
                      title="Ver producto"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </Link>
                    <Link
                      :href="route('productos.edit', producto.id_producto)"
                      class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
                      title="Editar producto"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </Link>
                    <button
                      @click="deleteProducto(producto)"
                      class="p-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200"
                      title="Eliminar producto"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!productos.data || productos.data.length === 0" class="text-center py-12">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-8">
            <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No hay productos</h3>
            <p class="text-purple-300 mb-4">No se encontraron productos que coincidan con tu búsqueda.</p>
            <Link
              :href="route('productos.create')"
              class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200"
            >
              Crear primer producto
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="productos.links && productos.links.length > 3" class="mt-8">
          <div class="flex justify-center">
            <nav class="flex space-x-2">
              <Link
                v-for="link in productos.links"
                :key="link.label"
                :href="link.url || '#'"
                :class="[
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                  !link.url || link.url === '#'
                    ? 'text-gray-500 cursor-not-allowed'
                    : link.active
                    ? 'bg-purple-600 text-white'
                    : 'bg-black/20 text-white hover:bg-purple-600/50'
                ]"
                v-html="link.label"
              />
            </nav>
          </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div class="bg-gray-900 rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-semibold text-white mb-4">Confirmar eliminación</h3>
            <p class="text-gray-300 mb-6">
              ¿Estás seguro de que quieres eliminar el producto "{{ productoToDelete?.nombre }}"?
            </p>
            <div class="flex space-x-4">
              <button
                @click="showDeleteModal = false"
                class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
              >
                Cancelar
              </button>
              <button
                @click="confirmDelete"
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200"
              >
                Eliminar
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

declare global {
  function route(name: string, params?: any): string
}

interface Producto {
  id_producto: number
  nombre: string
  descripcion: string
  precio_venta: number
  img_url?: string
  estado_disponible: string
  categoria?: {
    id_categoria: number
    nombre: string
  }
  marca?: {
    id_marca: number
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
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
}

interface Props {
  productos: PaginatedData
  categorias: Categoria[]
  marcas: Marca[]
}

const props = defineProps<Props>()

const search = ref('')
const selectedCategoria = ref('')
const selectedMarca = ref('')
const showDeleteModal = ref(false)
const productoToDelete = ref<Producto | null>(null)

let searchTimeout: NodeJS.Timeout | null = null

const debounceSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  const params: any = {}

  if (search.value) {
    params.search = search.value
  }

  if (selectedCategoria.value) {
    params.categoria = selectedCategoria.value
  }

  if (selectedMarca.value) {
    params.marca = selectedMarca.value
  }

  router.get(route('productos.index'), params, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  selectedCategoria.value = ''
  selectedMarca.value = ''
  applyFilters()
}

const deleteProducto = (producto: Producto) => {
  productoToDelete.value = producto
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (productoToDelete.value) {
    router.delete(route('productos.destroy', productoToDelete.value.id_producto), {
      onSuccess: (page) => {
        showDeleteModal.value = false
        productoToDelete.value = null

        const successDiv = document.createElement('div')
        successDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md'
        successDiv.innerHTML = `
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">Éxito</span>
          </div>
          <p class="mt-2 text-sm">Producto eliminado exitosamente</p>
        `
        document.body.appendChild(successDiv)
        setTimeout(() => {
          if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv)
          }
        }, 3000)
      },
      onError: (errors) => {
        showDeleteModal.value = false
        productoToDelete.value = null

        const errorMessage = errors.error || 'Error al eliminar el producto'
        const errorDiv = document.createElement('div')
        errorDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md'
        errorDiv.innerHTML = `
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">Error</span>
          </div>
          <p class="mt-2 text-sm">${errorMessage}</p>
        `
        document.body.appendChild(errorDiv)
        setTimeout(() => {
          if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv)
          }
        }, 5000)
      }
    })
  }
}

const getImageUrl = (imgUrl: string) => {
  if (!imgUrl) return ''

  // Si ya es una URL completa, devolverla tal como está
  if (imgUrl.startsWith('http://') || imgUrl.startsWith('https://')) {
    return imgUrl
  }

  // Si es una ruta relativa, construir la URL completa
  if (imgUrl.startsWith('/storage/')) {
    return `${window.location.origin}${imgUrl}`
  }

  // Si es solo el nombre del archivo, construir la ruta completa
  if (!imgUrl.startsWith('/')) {
    return `${window.location.origin}/storage/productos/${imgUrl}`
  }

  return imgUrl
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  console.error('Error cargando imagen:', img.src)

  // Intentar con la URL base del servidor
  const originalSrc = img.src
  if (originalSrc.includes('localhost:8000')) {
    img.src = originalSrc.replace('localhost:8000', window.location.host)
    return
  }

  // Si sigue fallando, intentar con cache busting
  if (!originalSrc.includes('?v=')) {
    img.src = originalSrc + '?v=' + Date.now()
    return
  }

  // Como último recurso, mostrar la imagen original sin modificaciones
  console.log('Mostrando imagen original sin modificaciones')
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
