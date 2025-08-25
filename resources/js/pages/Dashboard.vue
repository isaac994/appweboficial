<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Stats {
  total_productos: number
  total_categorias: number
  total_marcas: number
  total_proveedores: number
  total_clientes: number
}

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

interface Cliente {
  id_cliente: number
  nombre: string
  correo_electronico?: string
}

const props = defineProps<{
  stats: Stats
  productos_recientes: Producto[]
  clientes_recientes: Cliente[]
}>()

const getInitials = (name: string) => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const logout = () => {
  router.post(route('logout'))
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-purple-500/30', 'to-pink-500/30')
}
</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-purple-500/20">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-400">Total Productos</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_productos }}</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-blue-500/20">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-400">Categorías</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_categorias }}</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-green-500/20">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-400">Marcas</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_marcas }}</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-yellow-500/20">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-400">Proveedores</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_proveedores }}</p>
              </div>
            </div>
          </div>

          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-pink-500/20">
                <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-400">Clientes</p>
                <p class="text-2xl font-bold text-white">{{ stats.total_clientes }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Productos Recientes -->
        <div class="mb-8">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold text-white">Productos Recientes</h3>
              <Link
                :href="route('productos.index')"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
              >
                Ver Todos
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <div v-for="producto in productos_recientes" :key="producto.id_producto" class="bg-black/20 rounded-xl border border-purple-500/30 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
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
                <div class="p-4">
                  <h4 class="text-white font-semibold text-lg mb-2 line-clamp-1">{{ producto.nombre }}</h4>
                  <p v-if="producto.descripcion" class="text-purple-300 text-sm mb-3 line-clamp-2">{{ producto.descripcion }}</p>

                  <div class="space-y-1 mb-3">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-400">Categoría:</span>
                      <span class="text-white">{{ producto.categoria?.nombre || 'Sin categoría' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-400">Marca:</span>
                      <span class="text-white">{{ producto.marca?.nombre || 'Sin marca' }}</span>
                    </div>
                  </div>

                  <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-400 text-sm">Precio:</span>
                    <span class="text-purple-400 font-semibold">Bs {{ producto.precio_venta }}</span>
                  </div>
                  <Link
                    :href="route('productos.show', producto.id_producto)"
                    class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                  >
                    Ver
                  </Link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Clientes Recientes -->
        <div class="mt-8">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold text-white">Clientes Recientes</h3>
              <Link
                :href="route('clientes.index')"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
              >
                Ver Todos
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="cliente in clientes_recientes" :key="cliente.id_cliente" class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center mr-3">
                    <span class="text-white font-semibold">{{ getInitials(cliente.nombre) }}</span>
                  </div>
                  <div>
                    <h4 class="text-white font-medium">{{ cliente.nombre }}</h4>
                    <p v-if="cliente.correo_electronico" class="text-sm text-gray-400">{{ cliente.correo_electronico }}</p>
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
