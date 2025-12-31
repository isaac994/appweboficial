<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white">
              <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                Detalles del Producto
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Información completa del producto</p>
          </div>
          <div class="flex space-x-3">
            <Link
              :href="route('productos.edit', producto.id_producto)"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-300"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Editar
            </Link>
            <Link
              :href="route('productos.index')"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Volver
            </Link>
          </div>
        </div>

        <!-- Product Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Left Column - Image -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
            <div class="aspect-square bg-black/40 flex items-center justify-center overflow-hidden rounded-lg">
              <img
                v-if="producto.img_url"
                :src="producto.img_url"
                :alt="producto.descripcion"
                class="w-full h-full object-cover"
              />
              <svg v-else class="w-32 h-32 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
          </div>

          <!-- Right Column - Details -->
          <div class="space-y-6">
            <!-- Basic Info Card -->
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
              <h2 class="text-xl font-bold text-white mb-4">Información Básica</h2>

              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium text-blue-300">Modelo</label>
                  <p class="text-white font-semibold text-lg mt-1">{{ producto.modelo?.nombre || 'N/A' }}</p>
                </div>

                <div>
                  <label class="text-sm font-medium text-blue-300">Descripción</label>
                  <p class="text-white mt-1">{{ producto.descripcion }}</p>
                </div>

                <div>
                  <label class="text-sm font-medium text-blue-300">Precio de Venta</label>
                  <p class="text-2xl font-bold text-white mt-1">
                    Bs {{ parseFloat(producto.precio_venta).toFixed(2) }}
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="text-sm font-medium text-blue-300">Estado</label>
                    <span
                      :class="[
                        'inline-block mt-2 px-3 py-1 rounded-full text-sm font-medium',
                        producto.estado_disponible === 'disponible'
                          ? 'bg-green-500/20 text-green-400 border border-green-500/50'
                          : 'bg-red-500/20 text-red-400 border border-red-500/50'
                      ]"
                    >
                      {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                    </span>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-blue-300">Stock Disponible</label>
                    <p class="text-xl font-bold text-white mt-1">{{ producto.stock_disponible }} unidades</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Category & Brand Info Card -->
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
              <h2 class="text-xl font-bold text-white mb-4">Clasificación</h2>

              <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-blue-500/20">
                  <span class="text-blue-300 font-medium">Categoría</span>
                  <span class="text-white">{{ producto.categoria?.nombre || 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-blue-500/20">
                  <span class="text-blue-300 font-medium">Marca</span>
                  <span class="text-white">{{ producto.marca?.nombre || 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between py-2">
                  <span class="text-blue-300 font-medium">Modelo</span>
                  <span class="text-white">{{ producto.modelo?.nombre || 'N/A' }}</span>
                </div>
              </div>
            </div>

            <!-- Stock History Card -->
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
              <h2 class="text-xl font-bold text-white mb-4">Historial de Stock</h2>

              <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-blue-500/20">
                  <span class="text-blue-300 font-medium">Total Comprado</span>
                  <span class="text-green-400 font-bold">{{ producto.stock_total || 0 }} unidades</span>
                </div>

                <div class="flex items-center justify-between py-2 border-b border-blue-500/20">
                  <span class="text-blue-300 font-medium">Total Vendido</span>
                  <span class="text-blue-400 font-bold">{{ producto.total_ventas || 0 }} unidades</span>
                </div>

                <div class="flex items-center justify-between py-2">
                  <span class="text-blue-300 font-medium">Stock Disponible</span>
                  <span class="text-white font-bold">{{ producto.stock_disponible }} unidades</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-end space-x-4">
          <Link
            :href="route('productos.edit', producto.id_producto)"
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            Editar Producto
          </Link>
          <button
            @click="deleteProducto"
            class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            Eliminar Producto
          </button>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-black/90 border border-blue-500/30 rounded-xl p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-white mb-4">Confirmar Eliminación</h3>
          <p class="text-blue-300 mb-6">
            ¿Estás seguro de que quieres eliminar el producto "{{ producto.descripcion }}"? Esta acción no se puede deshacer.
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
  stock_total?: number
  total_ventas?: number
  categoria?: { nombre: string }
  marca?: { nombre: string }
  modelo?: { nombre: string }
}

const props = defineProps<{
  producto: Producto
}>()

const showDeleteModal = ref(false)

const deleteProducto = () => {
  showDeleteModal.value = true
}

const confirmDelete = () => {
  router.delete(route('productos.destroy', props.producto.id_producto), {
    onSuccess: () => {
      showDeleteModal.value = false
      router.visit(route('productos.index'))
    },
    onError: (errors) => {
      console.error('Error al eliminar producto:', errors)
      showDeleteModal.value = false
    }
  })
}
</script>

