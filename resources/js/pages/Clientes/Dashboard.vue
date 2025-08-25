<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Dashboard de Clientes</h1>
          <p class="mt-2 text-gray-600">Análisis completo del comportamiento y rendimiento de tus clientes</p>
        </div>

        <!-- Estadísticas Principales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.total_clientes }}</p>
                <p class="text-sm text-gray-500">+{{ stats.nuevos_este_mes }} este mes</p>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Ventas</p>
                <p class="text-3xl font-bold text-gray-900">${{ formatCurrency(stats.total_ventas) }}</p>
                <p class="text-sm text-gray-500">{{ stats.total_transacciones }} transacciones</p>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-lg">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Promedio por Cliente</p>
                <p class="text-3xl font-bold text-gray-900">${{ formatCurrency(stats.promedio_ventas) }}</p>
                <p class="text-sm text-gray-500">por transacción</p>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-lg">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Clientes Premium</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.clientes_premium }}</p>
                <p class="text-sm text-gray-500">{{ Math.round((stats.clientes_premium / stats.total_clientes) * 100) }}% del total</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Gráficos y Análisis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Distribución por Fidelización -->
          <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Fidelización</h3>
            <div class="space-y-3">
              <div v-for="nivel in fidelizacionData" :key="nivel.nombre" class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">{{ nivel.nombre }}</span>
                <div class="flex items-center space-x-2">
                  <span class="text-sm text-gray-500">{{ nivel.cantidad }} clientes</span>
                  <span class="text-sm font-semibold text-gray-900">{{ nivel.porcentaje }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Clientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Top Clientes por Ventas -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Clientes por Ventas</h3>
            <div class="space-y-3">
              <div v-for="(cliente, index) in topClientes" :key="cliente.id_cliente" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-bold text-blue-600">{{ index + 1 }}</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ cliente.nombre }}</p>
                    <p class="text-sm text-gray-500">{{ cliente.ventas_count }} ventas</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-semibold text-green-600">${{ formatCurrency(cliente.total_compras) }}</p>
                  <p class="text-xs text-gray-500">{{ cliente.estado_fidelizacion }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Clientes Recientes -->
          <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Clientes Recientes</h3>
            <div class="space-y-3">
              <div v-for="cliente in clientesRecientes" :key="cliente.id_cliente" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-bold text-green-600">{{ getInitials(cliente.nombre) }}</span>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ cliente.nombre }}</p>
                    <p class="text-sm text-gray-500">{{ formatDate(cliente.created_at) }}</p>
                  </div>
                </div>
                <Link
                  :href="route('clientes.show', cliente.id_cliente)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                  Ver
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Link
              :href="route('clientes.create')"
              class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors duration-200"
            >
              <div class="p-2 bg-blue-100 rounded-lg mr-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium text-blue-900">Nuevo Cliente</p>
                <p class="text-sm text-blue-600">Registrar cliente</p>
              </div>
            </Link>

            <Link
              :href="route('clientes.index')"
              class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors duration-200"
            >
              <div class="p-2 bg-green-100 rounded-lg mr-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium text-green-900">Ver Todos</p>
                <p class="text-sm text-green-600">Lista completa</p>
              </div>
            </Link>

            <Link
              :href="route('ventas.create')"
              class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors duration-200"
            >
              <div class="p-2 bg-purple-100 rounded-lg mr-3">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
              <div>
                <p class="font-medium text-purple-900">Nueva Venta</p>
                <p class="text-sm text-purple-600">Registrar venta</p>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Stats {
  total_clientes: number
  total_ventas: number
  total_transacciones: number
  promedio_ventas: number
  clientes_premium: number
  nuevos_este_mes: number
}

interface FidelizacionData {
  nombre: string
  cantidad: number
  porcentaje: number
}

interface GeneroData {
  nombre: string
  cantidad: number
  porcentaje: number
}

interface TopCliente {
  id_cliente: number
  nombre: string
  ventas_count: number
  total_compras: number
  estado_fidelizacion: string
}

interface ClienteReciente {
  id_cliente: number
  nombre: string
  created_at: string
}

const props = defineProps<{
  stats: Stats
  fidelizacionData: FidelizacionData[]
  generoData: GeneroData[]
  topClientes: TopCliente[]
  clientesRecientes: ClienteReciente[]
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
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(amount)
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

const getInitials = (name: string) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}
</script>
