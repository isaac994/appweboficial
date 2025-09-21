<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center">
                <Link
                  :href="route('clientes.index')"
                  class="text-blue-600 hover:text-blue-900 mr-4"
                >
                  ← Volver
                </Link>
                <h2 class="text-2xl font-semibold">Perfil del Cliente</h2>
              </div>
              <div class="flex space-x-3">
                <Link
                  :href="route('clientes.edit', cliente.id_cliente)"
                  class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                  Editar
                </Link>
                <Link
                  :href="route('ventas.create', { cliente_id: cliente.id_cliente })"
                  class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                  Nueva Venta
                </Link>
              </div>
            </div>

            <!-- Información del Cliente -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
              <!-- Información Personal -->
              <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm font-medium text-gray-500">ID del Cliente</p>
                    <p class="text-lg text-gray-900">#{{ cliente.id_cliente }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Cliente desde</p>
                    <p class="text-lg text-gray-900">{{ formatDate(cliente.created_at) }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Nombre</p>
                    <p class="text-lg text-gray-900">{{ cliente.nombre }} {{ cliente.apellidos || '' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">CI/NIT</p>
                    <p class="text-lg text-gray-900">{{ cliente.ci || 'No especificado' }}</p>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-500">Teléfono</p>
                    <p class="text-lg text-gray-900">{{ cliente.telefono || 'No especificado' }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h3>
                <div class="space-y-3">
                  <div>
                    <span class="font-medium text-gray-700">Total de Ventas:</span>
                    <span class="ml-2 text-gray-900">{{ cliente.ventas_count || 0 }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Total Gastado:</span>
                    <span class="ml-2 text-green-600 font-semibold">{{ formatCurrency(cliente.total_compras || 0) }}</span>
                  </div>
                  <div v-if="cliente.ultima_venta">
                    <span class="font-medium text-gray-700">Última Compra:</span>
                    <span class="ml-2 text-gray-900">{{ formatDate(cliente.ultima_venta.created_at) }}</span>
                  </div>
                </div>
              </div>


            </div>

            <!-- Historial de Ventas -->
            <div class="bg-gray-50 p-6 rounded-lg">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Historial de Ventas</h3>
                <span class="text-sm text-gray-500">{{ cliente.ventas_count || 0 }} ventas totales</span>
              </div>

              <div v-if="cliente.ventas && cliente.ventas.length > 0" class="space-y-4">
                <div v-for="venta in cliente.ventas" :key="venta.id_venta" class="border border-gray-200 rounded-lg p-4 bg-white">
                  <div class="flex justify-between items-start mb-3">
                    <div class="flex items-center space-x-3">
                      <span class="font-medium text-gray-700">Venta #{{ venta.id_venta }}</span>
                      <span class="text-sm text-gray-500">{{ formatDate(venta.created_at) }}</span>
                      <span :class="getEstadoVentaClass(venta.estado)" class="px-2 py-1 rounded-full text-xs font-medium">
                        {{ getEstadoVentaLabel(venta.estado) }}
                      </span>
                    </div>
                    <span class="font-semibold text-green-600 text-lg">{{ formatCurrency(venta.total) }}</span>
                  </div>

                  <div v-if="venta.detalles && venta.detalles.length > 0" class="text-sm text-gray-600">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                      <div v-for="detalle in venta.detalles" :key="detalle.id_detalle_venta" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <span>{{ detalle.producto?.nombre || 'Producto no disponible' }}</span>
                        <!-- Debug: {{ detalle.producto ? 'Producto cargado' : 'Producto NO cargado' }} -->
                        <span class="text-gray-500">x{{ detalle.cantidad }} - {{ formatCurrency(detalle.precio_unitario) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-gray-500 text-center py-8">
                <div class="text-4xl mb-2">📱</div>
                <p class="text-lg">Este cliente aún no tiene ventas registradas</p>
                <p class="text-sm text-gray-400">¡Crea su primera venta para comenzar!</p>
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
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface DetalleVenta {
  id_detalle_venta: number
  cantidad: number
  precio_unitario: number
  producto?: {
    nombre: string
  }
}

interface Venta {
  id_venta: number
  created_at: string
  total: number
  estado: string
  detalles?: DetalleVenta[]
}

interface Cliente {
  id_cliente: number
  nombre: string
  apellidos?: string
  ci?: string
  telefono?: string
  ventas?: Venta[]
  ventas_count?: number
  total_compras?: number
  ultima_venta?: Venta
  created_at: string
}

const props = defineProps<{
  cliente: Cliente
}>()

// Funciones helper
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number) => {
  // Verificar si el valor es válido
  if (amount === null || amount === undefined || isNaN(amount)) {
    return 'Bs 0.00';
  }

  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    currencyDisplay: 'symbol'
  }).format(amount).replace('BOB', 'Bs')
}

const getEstadoVentaLabel = (estado: string) => {
  const estados: Record<string, string> = {
    'completada': 'Completada',
    'pendiente': 'Pendiente',
    'cancelada': 'Cancelada'
  }
  return estados[estado] || estado
}

const getEstadoVentaClass = (estado: string) => {
  const clases: Record<string, string> = {
    'completada': 'bg-green-100 text-green-800',
    'pendiente': 'bg-yellow-100 text-yellow-800',
    'cancelada': 'bg-red-100 text-red-800'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800'
}

const getFidelizacionClass = (estado: string) => {
  const clases: Record<string, string> = {
    'Premium': 'bg-purple-100 text-purple-800',
    'Oro': 'bg-yellow-100 text-yellow-800',
    'Plata': 'bg-gray-100 text-gray-800',
    'Bronce': 'bg-orange-100 text-orange-800'
  }
  return clases[estado] || 'bg-gray-100 text-gray-800'
}
</script>
