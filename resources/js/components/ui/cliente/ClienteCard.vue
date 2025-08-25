<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="p-6">
      <!-- Header de la tarjeta -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-3">
            <span class="text-lg font-bold text-blue-600">{{ getInitials(cliente.nombre) }}</span>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ cliente.nombre }}</h3>
            <p class="text-sm text-gray-500">ID: {{ cliente.id_cliente }}</p>
          </div>
        </div>
        <div class="text-right">
          <span :class="getFidelizacionClass(cliente.estado_fidelizacion)" class="px-3 py-1 rounded-full text-xs font-medium">
            {{ cliente.estado_fidelizacion || 'Nuevo' }}
          </span>
        </div>
      </div>

      <!-- Información de contacto -->
      <div class="space-y-2 mb-4">
        <div v-if="cliente.telefono" class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
          </svg>
          {{ cliente.telefono }}
        </div>
        <div v-if="cliente.correo_electronico" class="flex items-center text-sm text-gray-600">
          <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
          </svg>
          {{ cliente.correo_electronico }}
        </div>
        <div v-if="cliente.direccion" class="flex items-start text-sm text-gray-600">
          <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
          <span class="line-clamp-2">{{ cliente.direccion }}</span>
        </div>
      </div>

      <!-- Información adicional -->
      <div class="grid grid-cols-2 gap-4 mb-4">
        <div v-if="cliente.fecha_nacimiento" class="text-center p-2 bg-gray-50 rounded">
          <p class="text-xs text-gray-500">Fecha de Nacimiento</p>
          <p class="text-sm font-medium text-gray-900">{{ formatDate(cliente.fecha_nacimiento) }}</p>
        </div>
        <div v-if="cliente.genero" class="text-center p-2 bg-gray-50 rounded">
          <p class="text-xs text-gray-500">Género</p>
          <p class="text-sm font-medium text-gray-900">{{ getGeneroLabel(cliente.genero) }}</p>
        </div>
      </div>

      <!-- Estadísticas -->
      <div class="border-t border-gray-200 pt-4">
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <p class="text-2xl font-bold text-blue-600">{{ cliente.ventas_count || 0 }}</p>
            <p class="text-xs text-gray-500">Ventas</p>
          </div>
          <div>
            <p class="text-2xl font-bold text-green-600">${{ formatCurrency(cliente.total_compras || 0) }}</p>
            <p class="text-sm text-gray-500">Total</p>
          </div>
          <div>
            <p class="text-2xl font-bold text-purple-600">{{ formatDate(cliente.created_at) }}</p>
            <p class="text-xs text-gray-500">Registrado</p>
          </div>
        </div>
      </div>

      <!-- Acciones -->
      <div class="flex space-x-2 mt-4 pt-4 border-t border-gray-200">
        <Link
          :href="route('clientes.show', cliente.id_cliente)"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200"
        >
          Ver Detalles
        </Link>
        <Link
          :href="route('clientes.edit', cliente.id_cliente)"
          class="flex-1 bg-gray-600 hover:bg-gray-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200"
        >
          Editar
        </Link>
        <button
          @click="$emit('delete', cliente)"
          class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors duration-200"
        >
          Eliminar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface Cliente {
  id_cliente: number
  nombre: string
  telefono?: string
  direccion?: string
  correo_electronico?: string
  fecha_nacimiento?: string
  genero?: string
  ventas_count?: number
  total_compras?: number
  estado_fidelizacion?: string
  created_at: string
}

const props = defineProps<{
  cliente: Cliente
}>()

defineEmits<{
  delete: [cliente: Cliente]
}>()

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('es-MX', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(amount)
}

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getGeneroLabel = (genero: string) => {
  const labels = {
    'M': 'Masculino',
    'F': 'Femenino',
    'O': 'Otro'
  }
  return labels[genero as keyof typeof labels] || genero
}

const getFidelizacionClass = (nivel: string) => {
  const classes = {
    'Bronce': 'bg-amber-100 text-amber-800',
    'Plata': 'bg-gray-100 text-gray-800',
    'Oro': 'bg-yellow-100 text-yellow-800',
    'Premium': 'bg-purple-100 text-purple-800'
  }
  return classes[nivel as keyof typeof classes] || 'bg-gray-100 text-gray-800'
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
