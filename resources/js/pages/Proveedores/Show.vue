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
                Detalles del Proveedor
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Información completa del proveedor</p>
          </div>
          <Link
            :href="route('proveedores.index')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
          </Link>
        </div>

        <!-- Provider Details -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Provider Information -->
            <div class="space-y-6">
              <div>
                <h2 class="text-2xl font-bold text-white mb-2">{{ proveedor.nombre }}</h2>
                <p class="text-purple-300 text-lg">{{ proveedor.ci_nit }}</p>
              </div>

              <!-- Provider Details -->
              <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4">
                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">ID del Proveedor</h4>
                    <p class="text-white font-semibold">#{{ proveedor.id_proveedor }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Nombre</h4>
                    <p class="text-white font-semibold">{{ proveedor.nombre }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">CI/NIT</h4>
                    <p class="text-white font-semibold font-mono">{{ proveedor.ci_nit }}</p>
                  </div>

                  <div class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                    <h4 class="text-sm font-medium text-gray-400 mb-1">Teléfono</h4>
                    <p class="text-white font-semibold">{{ proveedor.telefono }}</p>
                  </div>
                </div>
              </div>

            </div>

            <!-- Recent Purchases -->
            <div class="space-y-4">
              <h3 class="text-lg font-semibold text-white mb-4">Compras Recientes</h3>
              <div v-if="compras.length > 0" class="space-y-3">
                <div v-for="compra in compras" :key="compra.id_compra" class="bg-black/20 rounded-lg p-4 border border-purple-500/30">
                  <div class="flex justify-between items-center">
                    <div>
                      <h4 class="text-white font-medium">Compra #{{ compra.id_compra }}</h4>
                      <p class="text-purple-300 text-sm">{{ formatDate(compra.fecha) }}</p>
                      <p class="text-green-400 text-sm font-semibold">{{ formatCurrency(compra.total) }}</p>
                    </div>
                    <Link
                      :href="route('compras.show', compra.id_compra)"
                      class="px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                    >
                      Ver
                    </Link>
                  </div>
                </div>
              </div>
              <div v-else class="bg-black/20 rounded-lg p-6 border border-purple-500/30 text-center">
                <p class="text-gray-400">No hay compras registradas para este proveedor</p>
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
            ¿Estás seguro de que quieres eliminar el proveedor "{{ proveedor.nombre }}"? Esta acción no se puede deshacer.
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
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Proveedor {
  id_proveedor: number
  nombre: string
  ci_nit: string
  telefono: string
}

interface Compra {
  id_compra: number
  fecha: string
  total: number
}

const props = defineProps<{
  proveedor: Proveedor
  compras: Compra[]
}>()


const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number) => {
  return 'Bs ' + new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
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
