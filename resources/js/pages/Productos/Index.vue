<template>
  <ProductManagementLayout>
        <template #actions>
          <Link
            :href="route('productos.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nuevo Producto
          </Link>
        </template>


        <!-- Flash Messages -->
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


        <!-- Products Grid -->
        <div v-if="productos.data && productos.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 max-w-7xl mx-auto">
          <div
            v-for="producto in productos.data"
            :key="producto.id_producto"
            class="bg-black/20 backdrop-blur-xl rounded-lg border border-blue-500/30 overflow-hidden hover:border-blue-500/50 transition-all duration-300"
          >
            <!-- Product Image -->
            <div class="aspect-[4/3] bg-black/40 flex items-center justify-center overflow-hidden">
              <img
                v-if="producto.img_url"
                :src="producto.img_url"
                :alt="producto.descripcion"
                class="w-full h-full object-cover"
              />
              <svg v-else class="w-16 h-16 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>

            <!-- Product Info -->
            <div class="p-3">
              <h3 class="text-lg font-bold text-white mb-2 truncate">
                {{ producto.categoria?.nombre?.toLowerCase().includes('accesorio simple')
                   ? (producto.descripcion || 'Sin descripción')
                   : ((producto.marca?.nombre || '') + ' ' + (producto.modelo?.nombre || 'Sin modelo')) }}
              </h3>

              <div class="space-y-1 mb-3">
                <div v-if="!producto.categoria?.nombre?.toLowerCase().includes('accesorio simple')" class="flex items-center text-xs text-gray-300 truncate">
                  <span class="font-medium text-blue-300 mr-1">Descripción:</span>
                  <span class="truncate">{{ producto.descripcion || 'N/A' }}</span>
                </div>
              </div>

              <div class="flex items-center justify-between mb-3">
                <div class="text-lg font-bold text-white">
                  Bs {{ parseFloat(producto.precio_venta).toFixed(2) }}
                </div>
                <span
                  :class="[
                    'px-2 py-0.5 rounded-full text-xs font-medium',
                    producto.estado_disponible === 'disponible'
                      ? 'bg-green-500/20 text-green-400 border border-green-500/50'
                      : 'bg-red-500/20 text-red-400 border border-red-500/50'
                  ]"
                >
                  {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                </span>
              </div>

              <!-- Actions -->
              <div class="flex gap-2 justify-center">
                <Link
                  :href="route('productos.show', producto.id_producto)"
                  class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors duration-200"
                  title="Ver producto"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </Link>
                <Link
                  :href="route('productos.edit', producto.id_producto)"
                  class="p-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded transition-colors duration-200"
                  title="Editar producto"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </Link>
                <button
                  @click="deleteProducto(producto)"
                  class="p-2 bg-red-600 hover:bg-red-700 text-white rounded transition-colors duration-200"
                  title="Eliminar producto"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-8">
            <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No hay productos</h3>
            <p class="text-blue-300 mb-4">No se encontraron productos que coincidan con tu búsqueda.</p>
            <Link
              :href="route('productos.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
            >
              Crear primer producto
            </Link>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="productos.data && productos.data.length > 0" class="flex items-center justify-between mt-6">
          <div class="text-sm text-gray-300">
            Mostrando {{ productos.from }} a {{ productos.to }} de {{ productos.total }} productos
          </div>
          <div class="flex gap-2">
            <Link
              v-for="link in productos.links"
              :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200',
                link.active
                  ? 'bg-blue-600 text-white'
                  : link.url
                  ? 'bg-black/30 text-blue-300 hover:bg-blue-600/20 border border-blue-500/30'
                  : 'bg-black/20 text-gray-500 cursor-not-allowed'
              ]"
              :disabled="!link.url"
              v-html="link.label"
            />
          </div>
        </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-black/90 border border-blue-500/30 rounded-xl p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-white mb-4">Confirmar Eliminación</h3>
          <p class="text-blue-300 mb-6">
            ¿Estás seguro de que quieres eliminar el producto "{{ getProductoNombre(productoToDelete) }}"? Esta acción no se puede deshacer.
          </p>

          <!-- Error Message -->
          <div v-if="deleteError" class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-red-300 text-sm">{{ deleteError }}</p>
            </div>
          </div>

          <div class="flex space-x-3">
            <button
              @click="confirmDelete"
              class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors duration-200"
            >
              Eliminar
            </button>
            <button
              @click="showDeleteModal = false; deleteError = ''"
              class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
  </ProductManagementLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import ProductManagementLayout from '@/layouts/ProductManagementLayout.vue'

const route = (name: string, params?: any) => {
  return window.route(name, params)
}

interface Producto {
  id_producto: number
  descripcion: string
  precio_venta: string | number
  img_url?: string
  estado_disponible: string
  stock_disponible: number
  categoria?: { nombre: string }
  marca?: { nombre: string }
  modelo?: { nombre: string }
}

interface Categoria {
  id_categoria: number
  nombre: string
}

interface Modelo {
  id_modelo: number
  nombre: string
}

const props = defineProps<{
  productos: {
    data: Producto[]
    links: any[]
    from: number
    to: number
    total: number
  }
  categorias: Categoria[]
  modelos: Modelo[]
  filters: {
    search?: string
    categoria?: string
    marca?: string
    modelo?: string
    estado?: string
  }
}>()

const search = ref(props.filters.search || '')
const showDeleteModal = ref(false)
const productoToDelete = ref<Producto | null>(null)
const deleteError = ref('')

const debounceSearch = () => {
  router.get(route('productos.index'), {
    search: search.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearSearch = () => {
  search.value = ''
  debounceSearch()
}

// Función para obtener el nombre correcto del producto
const getProductoNombre = (producto: Producto | null) => {
  if (!producto) return 'Sin nombre'

  // Si es categoría "Accesorio Simple", usar la descripción como nombre principal
  if (producto.categoria?.nombre?.toLowerCase().includes('accesorio simple')) {
    return producto.descripcion || 'Sin descripción'
  }

  // Para otras categorías, mostrar "Marca Modelo"
  const marca = producto.marca?.nombre || ''
  const modelo = producto.modelo?.nombre || 'Sin modelo'
  const combinado = (marca + ' ' + modelo).trim()
  return combinado || 'Sin nombre'
}

const deleteProducto = (producto: Producto) => {
  productoToDelete.value = producto
  deleteError.value = '' // Limpiar errores anteriores
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (productoToDelete.value) {
    router.delete(route('productos.destroy', productoToDelete.value.id_producto), {
      onSuccess: () => {
        showDeleteModal.value = false
        productoToDelete.value = null
        deleteError.value = ''
      },
      onError: (errors) => {
        console.error('Error al eliminar producto:', errors)
        // Mostrar el error del backend de manera más clara
        if (errors.error) {
          deleteError.value = errors.error
        } else if (errors.message) {
          deleteError.value = errors.message
        } else {
          deleteError.value = 'No se puede eliminar este producto porque está siendo utilizado en otras operaciones del sistema.'
        }
      }
    })
  }
}
</script>

