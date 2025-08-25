<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

interface Producto {
  id_producto: number
  nombre: string
  descripcion?: string
  precio_venta: number
  img_url?: string
  categoria?: {
    nombre: string
  }
  marca?: {
    nombre: string
  }
}

const props = defineProps<{
  productosRecientes: Producto[]
}>()

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-purple-500/30', 'to-pink-500/30')
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Header -->
    <header class="bg-black/20 backdrop-blur-sm border-b border-purple-500/30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">
              <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Sistema de Inventario
              </span>
            </h1>
          </div>

          <!-- Navigation -->
          <nav class="flex items-center space-x-4">
            <Link
              :href="route('login')"
              class="px-4 py-2 text-gray-300 hover:text-white transition-colors duration-200"
            >
              Acceso
            </Link>
            <Link
              :href="route('register')"
              class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Registro
            </Link>
          </nav>
        </div>
      </div>
    </header>

    <!-- Products Showcase -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
          <h3 class="text-3xl font-bold text-white mb-4">
            <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
              Productos Recientes
            </span>
          </h3>
          <p class="text-gray-300 text-lg">
            Descubre nuestros productos más recientes y populares
          </p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="producto in productosRecientes"
            :key="producto.id_producto"
            class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-6">
              <h4 class="text-lg font-semibold text-white mb-2 line-clamp-1">{{ producto.nombre }}</h4>
              <p v-if="producto.descripcion" class="text-purple-300 text-sm mb-3 line-clamp-2">{{ producto.descripcion }}</p>

              <div class="space-y-2 mb-4">
                <div class="flex justify-between items-center">
                  <span class="text-gray-400 text-sm">Categoría:</span>
                  <span class="text-purple-300 text-sm">{{ producto.categoria?.nombre || 'Sin categoría' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400 text-sm">Marca:</span>
                  <span class="text-purple-300 text-sm">{{ producto.marca?.nombre || 'Sin marca' }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400 text-sm">Precio:</span>
                  <span class="text-green-400 font-semibold">Bs {{ producto.precio_venta }}</span>
                </div>
              </div>

              <!-- Call to Action -->
              <div class="text-center">
                <Link
                  :href="route('login')"
                  class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-medium rounded-lg transition-all duration-300 text-center"
                >
                  Ver Detalles
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="productosRecientes.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h3 class="text-xl font-semibold text-white mb-2">No hay productos disponibles</h3>
          <p class="text-purple-300 mb-4">Regístrate para comenzar a gestionar tu inventario</p>
          <Link
            :href="route('register')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Crear Cuenta
          </Link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black/20 backdrop-blur-sm border-t border-purple-500/30 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <p class="text-gray-300">
            © 2024 Sistema de Inventario. Todos los derechos reservados.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
