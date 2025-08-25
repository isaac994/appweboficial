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
                Detalles de la Marca
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Información completa de la marca</p>
          </div>
          <Link
            :href="route('marcas.index')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
          </Link>
        </div>

        <!-- Brand Details -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Brand Information -->
            <div class="space-y-6">
              <div>
                <h2 class="text-2xl font-bold text-white mb-2">{{ marca.nombre }}</h2>
                <p v-if="marca.pais_origen" class="text-purple-300 text-lg">País de origen: {{ marca.pais_origen }}</p>
              </div>

              <!-- Brand Details -->
              <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4">
                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">ID de la Marca</h4>
                    <p class="text-white font-semibold">#{{ marca.id_marca }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Nombre</h4>
                    <p class="text-white font-semibold">{{ marca.nombre }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">País de Origen</h4>
                    <p class="text-white font-semibold">{{ marca.pais_origen || 'No especificado' }}</p>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex space-x-4 pt-6">
                <Link
                  :href="route('marcas.edit', marca.id_marca)"
                  class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-300 text-center"
                >
                  <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  Editar Marca
                </Link>
                <button
                  @click="deleteMarca"
                  class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold rounded-lg transition-all duration-300"
                >
                  <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  Eliminar Marca
                </button>
              </div>
            </div>

            <!-- Products in this brand -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold text-white mb-4">Productos de esta Marca</h3>
              <div v-if="productos.length > 0" class="space-y-3">
                <div v-for="producto in productos" :key="producto.id_producto" class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                  <div class="flex justify-between items-center">
                    <div>
                      <h4 class="text-white font-medium">{{ producto.nombre }}</h4>
                      <p class="text-purple-300 text-sm">Bs {{ producto.precio_venta }}</p>
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
              <div v-else class="bg-black/20 rounded-lg p-6 border border-purple-500/30 text-center">
                <p class="text-gray-400">No hay productos de esta marca</p>
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
            ¿Estás seguro de que quieres eliminar la marca "{{ marca.nombre }}"? Esta acción no se puede deshacer.
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

interface Marca {
  id_marca: number
  nombre: string
  pais_origen?: string
}

interface Producto {
  id_producto: number
  nombre: string
  precio_venta: number
}

const props = defineProps<{
  marca: Marca
  productos: Producto[]
}>()

const showDeleteModal = ref(false)

const deleteMarca = () => {
  showDeleteModal.value = true
}

const confirmDelete = () => {
  router.delete(route('marcas.destroy', props.marca.id_marca), {
    onSuccess: () => {
      showDeleteModal.value = false
    }
  })
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
