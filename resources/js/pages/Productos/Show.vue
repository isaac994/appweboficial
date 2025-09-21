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
                    <h4 class="text-sm font-medium text-gray-400 mb-1">ID del Producto</h4>
                    <p class="text-white font-semibold">#{{ producto.id_producto }}</p>
                  </div>
                </div>

                <!-- Pricing and Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Precio de Venta</h4>
                    <p class="text-purple-400 font-bold text-xl">Bs {{ producto.precio_venta }}</p>
                  </div>

                  <div class="bg-gradient-to-r from-green-500/20 to-green-600/20 rounded-lg p-4 border border-green-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Estado</h4>
                    <p class="font-bold text-xl" :class="producto.estado_disponible === 'disponible' ? 'text-green-400' : 'text-red-400'">
                      {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                    </p>
                  </div>
                </div>


              </div>

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

interface Producto {
  id_producto: number
  nombre: string
  descripcion?: string
  precio_venta: number
  estado: string
  estado_disponible: string
  img_url?: string
  categoria?: {
    nombre: string
  }
  marca?: {
    nombre: string
  }
}

const props = defineProps<{
  producto: Producto
}>()


const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-purple-500/30', 'to-pink-500/30')
}

</script>
