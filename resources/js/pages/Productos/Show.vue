<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white">
              <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Detalles del Producto
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Información completa del producto</p>
          </div>
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

        <!-- Product Details -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Product Image -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold text-white mb-4">Imagen del Producto</h3>
              <div class="aspect-square bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-xl border border-purple-500/30 overflow-hidden">
                <img
                  v-if="producto.img_url"
                  :src="producto.img_url + '?v=' + Date.now()"
                  :alt="producto.nombre"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <div v-else class="w-full h-full bg-gradient-to-br from-purple-500/30 to-pink-500/30 flex items-center justify-center">
                  <svg class="w-32 h-32 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Product Information -->
            <div class="space-y-6">
              <div>
                <h2 class="text-2xl font-bold text-white mb-2">{{ producto.nombre }}</h2>
                <p v-if="producto.descripcion" class="text-purple-300 text-lg">{{ producto.descripcion }}</p>
              </div>

              <!-- Product Details -->
              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Categoría</h4>
                    <p class="text-white font-semibold">{{ producto.categoria?.nombre || 'Sin categoría' }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Marca</h4>
                    <p class="text-white font-semibold">{{ producto.marca?.nombre || 'Sin marca' }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Proveedor</h4>
                    <p class="text-white font-semibold">{{ producto.proveedor?.nombre || 'Sin proveedor' }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">ID del Producto</h4>
                    <p class="text-white font-semibold">#{{ producto.id_producto }}</p>
                  </div>
                </div>

                <!-- Pricing -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="bg-gradient-to-r from-green-500/20 to-green-600/20 rounded-lg p-4 border border-green-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Precio de Compra</h4>
                    <p class="text-green-400 font-bold text-xl">Bs {{ producto.precio_compra }}</p>
                  </div>

                  <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Precio de Venta</h4>
                    <p class="text-purple-400 font-bold text-xl">Bs {{ producto.precio_venta }}</p>
                  </div>
                </div>

                <!-- Profit Calculation -->
                <div class="bg-gradient-to-r from-blue-500/20 to-blue-600/20 rounded-lg p-4 border border-blue-500/30">
                  <h4 class="text-sm font-medium text-gray-400 mb-1">Margen de Ganancia</h4>
                  <p class="text-blue-400 font-bold text-xl">Bs {{ (producto.precio_venta - producto.precio_compra).toFixed(2) }}</p>
                  <p class="text-blue-300 text-sm">
                    {{ ((producto.precio_venta - producto.precio_compra) / producto.precio_compra * 100).toFixed(1) }}% de ganancia
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex space-x-4 pt-6">
                <Link
                  :href="route('productos.edit', producto.id_producto)"
                  class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-300 text-center"
                >
                  <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Editar Producto
                </Link>
                <button
                  @click="deleteProducto"
                  class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg transition-all duration-300"
                >
                  <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  Eliminar Producto
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-black/90 border border-purple-500/30 rounded-xl p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-semibold text-white mb-4">Confirmar Eliminación</h3>
          <p class="text-purple-300 mb-6">
            ¿Estás seguro de que quieres eliminar el producto "{{ producto.nombre }}"? Esta acción no se puede deshacer.
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

const props = defineProps<{
  producto: Producto
}>()

const showDeleteModal = ref(false)

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-purple-500/30', 'to-pink-500/30')
}

const deleteProducto = () => {
  showDeleteModal.value = true
}

const confirmDelete = () => {
  router.delete(route('productos.destroy', props.producto.id_producto), {
    onSuccess: () => {
      showDeleteModal.value = false
    }
  })
}
</script>
