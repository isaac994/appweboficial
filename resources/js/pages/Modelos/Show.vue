<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">{{ modelo.nombre }}</h1>
            <p class="text-blue-300">Detalles del modelo</p>
          </div>
          <div class="flex space-x-3">
            <Link
              :href="route('modelos.edit', modelo.id_modelo)"
              class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Editar
            </Link>
            <Link
              :href="route('modelos.index')"
              class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Volver
            </Link>
          </div>
        </div>

        <!-- Model Details -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-6">
              <!-- Nombre -->
              <div>
                <label class="block text-sm font-medium text-blue-300 mb-2">Nombre del Modelo</label>
                <div class="text-xl font-semibold text-white">{{ modelo.nombre }}</div>
              </div>

              <!-- Marca -->
              <div>
                <label class="block text-sm font-medium text-blue-300 mb-2">Marca</label>
                <div class="flex items-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ modelo.marca?.nombre }}
                  </span>
                </div>
              </div>

              <!-- Descripción -->
              <div>
                <label class="block text-sm font-medium text-blue-300 mb-2">Descripción</label>
                <div class="text-white">
                  <p v-if="modelo.descripcion" class="whitespace-pre-wrap">{{ modelo.descripcion }}</p>
                  <p v-else class="text-gray-400 italic">Sin descripción</p>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
              <!-- Fecha de Creación -->
              <div>
                <label class="block text-sm font-medium text-blue-300 mb-2">Fecha de Creación</label>
                <div class="text-white">{{ formatDate(modelo.created_at) }}</div>
              </div>

              <!-- Última Actualización -->
              <div>
                <label class="block text-sm font-medium text-blue-300 mb-2">Última Actualización</label>
                <div class="text-white">{{ formatDate(modelo.updated_at) }}</div>
              </div>

              <!-- ID del Modelo -->
              <div>
                <label class="block text-sm font-medium text-blue-300 mb-2">ID del Modelo</label>
                <div class="text-white font-mono bg-black/30 px-3 py-2 rounded">{{ modelo.id_modelo }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Related Information -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Marca Information -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Información de la Marca</h3>
            <div v-if="modelo.marca" class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-blue-300">Nombre</label>
                <div class="text-white">{{ modelo.marca.nombre }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-blue-300">ID de Marca</label>
                <div class="text-white font-mono bg-black/30 px-3 py-2 rounded">{{ modelo.marca.id_marca }}</div>
              </div>
            </div>
            <div v-else class="text-gray-400 italic">
              No hay información de marca disponible
            </div>
          </div>

          <!-- Statistics -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Estadísticas</h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-blue-300">Productos asociados:</span>
                <span class="text-white font-semibold">{{ modelo.productos?.length || 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-blue-300">Estado:</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Activo
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Section -->
        <div v-if="modelo.productos && modelo.productos.length > 0" class="mt-8">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Productos de este Modelo</h3>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-purple-600/20">
                  <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-white">Producto</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-white">Precio</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-white">Categoría</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-purple-500/20">
                  <tr v-for="producto in modelo.productos" :key="producto.id_producto" class="hover:bg-purple-500/10">
                    <td class="px-4 py-3">
                      <div class="text-white font-medium">{{ producto.modelo?.nombre || 'Sin modelo' }}</div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="text-green-400 font-semibold">Bs {{ producto.precio_venta }}</div>
                    </td>
                    <td class="px-4 py-3">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ producto.categoria?.nombre }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Empty Products State -->
        <div v-else class="mt-8">
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-8 text-center">
            <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-white mb-2">No hay productos</h3>
            <p class="text-blue-300">Este modelo no tiene productos asociados aún.</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// Helper para generar rutas
const route = (name: string, params?: any) => {
  return window.route(name, params)
}

interface Producto {
  id_producto: number
  nombre: string
  precio_venta: number
  categoria?: {
    id_categoria: number
    nombre: string
  }
}

interface Modelo {
  id_modelo: number
  nombre: string
  descripcion?: string
  id_marca: number
  created_at: string
  updated_at: string
  marca?: {
    id_marca: number
    nombre: string
  }
  productos?: Producto[]
}

const props = defineProps<{
  modelo: Modelo
}>()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
