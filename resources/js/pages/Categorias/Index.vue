<template>
  <ProductManagementLayout>
        <!-- Action Button -->
        <template #actions>
          <Link
            :href="route('categorias.create')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Nueva Categoría
          </Link>
        </template>

        <!-- Error Messages -->
        <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mb-6">
          <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h3 class="text-red-400 font-semibold">Error</h3>
            </div>
            <ul class="mt-2 text-red-300 text-sm">
              <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
            </ul>
          </div>
        </div>

        <!-- Flash Error Messages -->
        <div v-if="$page.props.flash && $page.props.flash.error" class="mb-6">
          <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-red-400 font-semibold">{{ $page.props.flash.error }}</p>
            </div>
          </div>
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


        <!-- Categories Table -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-blue-600/20">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-white">Categoría</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-white">Descripción</th>
                  <th class="px-6 py-4 text-center text-sm font-semibold text-white">Productos</th>
                  <th class="px-6 py-4 text-center text-sm font-semibold text-white">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-purple-500/20">
                <tr
                  v-for="categoria in categorias"
                  :key="categoria.id_categoria"
                  class="hover:bg-blue-500/10 transition-colors duration-200"
                >
                  <td class="px-6 py-4">
                    <div class="text-white font-medium">{{ categoria.nombre }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-gray-300">{{ categoria.descripcion || 'Sin descripción' }}</div>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                      {{ categoria.productos_count }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex justify-center space-x-2">
                      <Link
                        :href="route('categorias.show', categoria.id_categoria)"
                        class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors duration-200"
                        title="Ver categoría"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </Link>
                      <Link
                        :href="route('categorias.edit', categoria.id_categoria)"
                        class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors duration-200"
                        title="Editar categoría"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </Link>
                      <button
                        @click="deleteCategoria(categoria)"
                        class="p-2 bg-red-600 hover:bg-red-700 text-white rounded transition-colors duration-200"
                        title="Eliminar categoría"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!categorias || categorias.length === 0" class="text-center py-12">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-8">
            <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No hay categorías</h3>
            <p class="text-blue-300 mb-4">No se encontraron categorías que coincidan con tu búsqueda.</p>
            <Link
              :href="route('categorias.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
            >
              Crear primera categoría
            </Link>
          </div>
        </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-black/90 border border-blue-500/30 rounded-xl p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-white mb-4">Confirmar Eliminación</h3>
          <p class="text-blue-300 mb-6">
            ¿Estás seguro de que quieres eliminar la categoría "{{ categoriaToDelete?.nombre }}"? Esta acción no se puede deshacer.
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
  </ProductManagementLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import ProductManagementLayout from '@/layouts/ProductManagementLayout.vue'

// Helper para generar rutas
const route = (name: string, params?: any) => {
  return window.route(name, params)
}

interface Categoria {
  id_categoria: number
  nombre: string
  descripcion?: string
  productos_count: number
}

const props = defineProps<{
  categorias: Categoria[]
  filters: {
    search?: string
  }
}>()

const search = ref(props.filters.search || '')
const showDeleteModal = ref(false)
const categoriaToDelete = ref<Categoria | null>(null)

const debounceSearch = () => {
  router.get(route('categorias.index'), {
    search: search.value
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const clearFilters = () => {
  search.value = ''
  router.get(route('categorias.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const deleteCategoria = async (categoria: Categoria) => {
  try {
    const response = await fetch(route('categorias.can-delete', categoria.id_categoria))
    const data = await response.json()

    if (!data.can_delete) {
      const errorMessage = `No se puede eliminar la categoría '${data.categoria_nombre}' porque tiene ${data.productos_count} producto(s) asociado(s). Primero debe eliminar o cambiar la categoría de estos productos.`

      router.get(route('categorias.index'), {}, {
        onSuccess: () => {
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
      return
    }

    categoriaToDelete.value = categoria
    showDeleteModal.value = true
  } catch (error) {
    console.error('Error al verificar si se puede eliminar la categoría:', error)
    categoriaToDelete.value = categoria
    showDeleteModal.value = true
  }
}

const confirmDelete = () => {
  if (categoriaToDelete.value) {
    router.delete(route('categorias.destroy', categoriaToDelete.value.id_categoria), {
      onSuccess: (page) => {
        showDeleteModal.value = false
        categoriaToDelete.value = null

        // Mostrar mensaje de éxito
        const successDiv = document.createElement('div')
        successDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md'
        successDiv.innerHTML = `
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">Éxito</span>
          </div>
          <p class="mt-2 text-sm">Categoría eliminada exitosamente</p>
        `
        document.body.appendChild(successDiv)
        setTimeout(() => {
          if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv)
          }
        }, 3000)
      },
      onError: (errors) => {
        console.error('Error al eliminar categoría:', errors)
      }
    })
  }
}
</script>
