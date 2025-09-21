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

        <!-- Filters -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="flex space-x-4">
            <div class="flex-1">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar proveedores por nombre, CI/NIT o teléfono..."
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

        <!-- Providers Table -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-purple-600/20">
                <tr>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-white">Proveedor</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-white">CI/NIT</th>
                  <th class="px-6 py-4 text-left text-sm font-semibold text-white">Teléfono</th>
                  <th class="px-6 py-4 text-center text-sm font-semibold text-white">Compras</th>
                  <th class="px-6 py-4 text-center text-sm font-semibold text-white">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-purple-500/20">
                <tr
                  v-for="proveedor in proveedores"
                  :key="proveedor.id_proveedor"
                  class="hover:bg-purple-500/10 transition-colors duration-200"
                >
                  <td class="px-6 py-4">
                    <div class="text-white font-medium">{{ proveedor.nombre }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-gray-300 font-mono">{{ proveedor.ci_nit }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-gray-300">{{ proveedor.telefono }}</div>
                  </td>
                  <td class="px-6 py-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                      {{ proveedor.compras_count }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex justify-center space-x-2">
                      <Link
                        :href="route('proveedores.show', proveedor.id_proveedor)"
                        class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded transition-colors duration-200"
                        title="Ver proveedor"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </Link>
                      <Link
                        :href="route('proveedores.edit', proveedor.id_proveedor)"
                        class="p-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors duration-200"
                        title="Editar proveedor"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </Link>
                      <button
                        @click="deleteProveedor(proveedor)"
                        class="p-2 bg-red-600 hover:bg-red-700 text-white rounded transition-colors duration-200"
                        title="Eliminar proveedor"
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
        <div v-if="!proveedores || proveedores.length === 0" class="text-center py-12">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-8">
            <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No hay proveedores</h3>
            <p class="text-purple-300 mb-4">No se encontraron proveedores que coincidan con tu búsqueda.</p>
            <Link
              :href="route('proveedores.create')"
              class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200"
            >
              Crear primer proveedor
            </Link>
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

// Helper para generar rutas
const route = (name: string, params?: any) => {
  return window.route(name, params)
}

interface Proveedor {
  id_proveedor: number
  nombre: string
  ci_nit: string
  telefono: string
  compras_count: number
}

const props = defineProps<{
  proveedores: Proveedor[]
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

const deleteProveedor = async (proveedor: Proveedor) => {
  try {
    // Verificar si tiene compras asociadas
    if (proveedor.compras_count > 0) {
      const errorMessage = `No se puede eliminar el proveedor '${proveedor.nombre}' porque tiene ${proveedor.compras_count} compra(s) asociada(s). Primero debe eliminar o cambiar el proveedor de estas compras.`

      router.get(route('proveedores.index'), {}, {
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

    proveedorToDelete.value = proveedor
    showDeleteModal.value = true
  } catch (error) {
    console.error('Error al verificar si se puede eliminar el proveedor:', error)
    proveedorToDelete.value = proveedor
    showDeleteModal.value = true
  }
}

const confirmDelete = () => {
  if (proveedorToDelete.value) {
    router.delete(route('proveedores.destroy', proveedorToDelete.value.id_proveedor), {
      onSuccess: () => {
        showDeleteModal.value = false
        proveedorToDelete.value = null

        // Mostrar mensaje de éxito inmediatamente
        const successDiv = document.createElement('div')
        successDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md'
        successDiv.innerHTML = `
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">Éxito</span>
          </div>
          <p class="mt-2 text-sm">Proveedor eliminado exitosamente</p>
        `
        document.body.appendChild(successDiv)
        setTimeout(() => {
          if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv)
          }
        }, 3000)
      },
      onError: (errors) => {
        console.error('Error al eliminar proveedor:', errors)
        showDeleteModal.value = false
        proveedorToDelete.value = null

        // Mostrar mensaje de error
        const errorDiv = document.createElement('div')
        errorDiv.className = 'fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white p-4 rounded-lg shadow-lg z-50 max-w-md'
        errorDiv.innerHTML = `
          <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">Error</span>
          </div>
          <p class="mt-2 text-sm">Error al eliminar el proveedor</p>
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
</script>
