<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import VentasChart from '@/components/VentasChart.vue'

interface Stats {
  total_productos: number
  total_categorias: number
  total_marcas: number
  total_proveedores: number
  total_clientes: number
}

interface StatsMejoradas {
  ganancias_hoy: number
  ventas_cantidad_hoy: number
  ganancias_semana: number
  productos_mas_vendidos: Array<{
    nombre: string
    precio_venta: number
    total_vendido: number
  }>
}

interface Producto {
  id_producto: number
  nombre: string
  descripcion?: string
  precio_venta: number
  estado_disponible: string
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

interface VentasSemanales {
  labels: string[]
  data: number[]
  total_semana: number
  promedio_diario: number
  dia_mayor_venta: string
  mayor_venta: number
  fecha_inicio: string
  fecha_fin: string
  semanas_atras: number
}

const props = defineProps<{
  stats: Stats
  stats_mejoradas: StatsMejoradas
  productos_recientes: Producto[]
  clientes_recientes: Cliente[]
  ventas_semanales?: VentasSemanales
  ventas_semana_anterior?: VentasSemanales
  ventas_semana_anterior2?: VentasSemanales
}>()

const getInitials = (name: string) => {
  if (!name) return 'U'
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
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-blue-500/30', 'to-cyan-500/30')
}

// Estado para controlar la visibilidad de la tarjeta de bienvenida
const showWelcomeCard = ref(true)

// Ocultar la tarjeta de bienvenida después de 5 segundos
onMounted(() => {
  setTimeout(() => {
    showWelcomeCard.value = false
  }, 5000)
  // Auto-actualizar ingresos del día cada 60s sin recargar toda la página
  const interval = setInterval(() => {
    router.get(route('dashboard'), {}, { preserveState: true, replace: true, only: ['stats_mejoradas'] })
  }, 60000)
  onUnmounted(() => clearInterval(interval))
})
</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <!-- Header -->
      <div class="bg-[#0a1628]/80 backdrop-blur-xl border-b border-blue-500/20">
        <div class="px-10 py-6">
          <div class="flex items-center justify-between">
            <!-- Left side - Inicio link -->
            <div class="flex items-center space-x-2">
              <span class="text-3xl font-semibold text-blue-400">Inicio</span>
              <svg class="w-4 h-4 text-blue-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span class="text-sm text-gray-400">Material de Apoyo</span>
            </div>

            <!-- Right side - User info -->
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center">
                <span class="text-white font-semibold text-sm">{{ getInitials($page.props.auth.user.name) }}</span>
              </div>
              <span class="text-white font-medium">{{ $page.props.auth.user.name }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Card -->
        <transition
          enter-active-class="transition-all duration-500 ease-out"
          enter-from-class="opacity-0 transform translate-y-4"
          enter-to-class="opacity-100 transform translate-y-0"
          leave-active-class="transition-all duration-500 ease-in"
          leave-from-class="opacity-100 transform translate-y-0"
          leave-to-class="opacity-0 transform -translate-y-4"
        >
          <div v-if="showWelcomeCard" class="bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-8 mb-8">
            <h1 class="text-3xl font-bold text-white mb-4">Bienvenido</h1>
            <p class="text-gray-300">Gestiona tu inventario de celulares y accesorios de manera eficiente, realiza un seguimiento de ventas y mantén informados a tus clientes con nuestro sistema integral</p>
          </div>
        </transition>

        <!-- Estadísticas Principales -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Total Productos -->
          <div class="group bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-6 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
              </div>
            </div>
            <p class="text-sm text-gray-400 mb-1">Total Productos</p>
            <p class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
              {{ stats.total_productos }}
            </p>
            <p class="text-xs text-gray-500 mt-1">En inventario</p>
          </div>

          <!-- Total Clientes -->
          <div class="group bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-6 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 rounded-xl bg-gradient-to-br from-cyan-500/20 to-teal-500/20 border border-cyan-500/30">
                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
            </div>
            <p class="text-sm text-gray-400 mb-1">Total Clientes</p>
            <p class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-teal-400 bg-clip-text text-transparent">
              {{ stats.total_clientes }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Registrados</p>
          </div>

          <!-- Ingresos del Día -->
          <div class="group bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-6 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 rounded-xl bg-gradient-to-br from-green-500/20 to-emerald-500/20 border border-green-500/30">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div class="px-3 py-1 bg-green-500/10 rounded-full border border-green-500/30">
                <span class="text-xs text-green-400 font-semibold">{{ stats_mejoradas.ventas_cantidad_hoy }} ventas</span>
              </div>
            </div>
            <p class="text-sm text-gray-400 mb-1">Ingresos del día</p>
            <p class="text-3xl font-bold bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
              Bs {{ stats_mejoradas.ganancias_hoy.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </p>
          </div>
        </div>

        <!-- Gráfico de Ventas y Productos Más Vendidos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
          <!-- Gráfico de Ventas Semanales -->
          <div class="lg:col-span-2">
            <VentasChart
              :ventas-data="ventas_semanales"
              :ventas-semana-anterior="ventas_semana_anterior"
              :ventas-semana-anterior2="ventas_semana_anterior2"
            />
          </div>

          <!-- Productos Más Vendidos del Día -->
          <div v-if="stats_mejoradas.productos_mas_vendidos.length > 0">
          <div class="bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-6 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center space-x-3">
                <div class="p-2 rounded-xl bg-gradient-to-br from-yellow-500/20 to-orange-500/20 border border-yellow-500/30">
                  <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                </div>
                <div>
                  <h3 class="text-xl font-semibold text-white">Productos Más Vendidos Hoy</h3>
                  <p class="text-sm text-gray-400">{{ new Date().toLocaleDateString('es-ES', { weekday: 'long', day: 'numeric', month: 'long' }) }}</p>
                </div>
              </div>
            </div>
            <div class="flex flex-col space-y-3">
              <div v-for="(producto, index) in stats_mejoradas.productos_mas_vendidos" :key="index"
                   class="flex items-center justify-between bg-[#0a1628]/60 rounded-xl p-4 border border-blue-500/20 hover:border-yellow-500/50 transition-all duration-300">
                <div class="flex items-center space-x-4 min-w-0">
                  <div class="min-w-0">
                    <h4 class="text-white font-semibold truncate">{{ (producto.marca || 'Sin marca') + ' ' + (producto.nombre || '') }}</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-500/10 text-yellow-400 font-semibold rounded-full text-xs border border-yellow-500/30">
                      {{ producto.total_vendido }} vendidos
                    </span>
                  </div>
                </div>
                <div class="text-lg font-bold bg-gradient-to-r from-yellow-400 to-orange-400 bg-clip-text text-transparent flex-shrink-0">
                  Bs {{ producto.precio_venta.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>

        <!-- Productos Recientes -->
        <div class="mb-8">
          <div class="bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-6 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center space-x-3">
                <div class="p-2 rounded-xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/30">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-white">Productos Recientes</h3>
              </div>
              <Link
                :href="route('productos.index')"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all duration-300 hover:-translate-y-0.5"
              >
                <span>Ver Todos</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div v-for="producto in productos_recientes" :key="producto.id_producto"
                   class="group bg-[#0a1628]/60 rounded-xl border border-blue-500/20 overflow-hidden hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/20 transition-all duration-300 hover:-translate-y-2">
                <!-- Product Image -->
                <div class="relative aspect-square bg-gradient-to-br from-blue-500/10 to-cyan-500/10 overflow-hidden">
                  <img
                    v-if="producto.img_url"
                    :src="producto.img_url + '?v=' + Date.now()"
                    :alt="producto.modelo?.nombre || 'Producto'"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                    @error="handleImageError"
                  />
                  <div v-else class="w-full h-full bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center">
                    <svg class="w-16 h-16 text-blue-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                  </div>

                  <!-- Status Badge -->
                  <div class="absolute top-3 right-3">
                    <span
                      :class="producto.estado_disponible === 'disponible'
                        ? 'bg-green-500/90'
                        : 'bg-red-500/90'"
                      class="px-3 py-1 rounded-full text-xs font-bold text-white backdrop-blur-xl border border-white/20"
                    >
                      {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                    </span>
                  </div>

                  <!-- Overlay on Hover -->
                  <div class="absolute inset-0 bg-gradient-to-t from-[#0a1628] via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>

                <!-- Product Info -->
                <div class="p-4 space-y-3">
                  <div>
                    <h4 class="text-white font-semibold text-base mb-1 line-clamp-1 group-hover:text-blue-400 transition-colors">
                      {{ producto.categoria?.nombre?.toLowerCase().includes('accesorio simple')
                         ? (producto.descripcion || 'Sin descripción')
                         : ((producto.marca?.nombre || '') + ' ' + (producto.modelo?.nombre || 'Sin modelo')) }}
                    </h4>
                    <p v-if="!producto.categoria?.nombre?.toLowerCase().includes('accesorio simple') && producto.descripcion" class="text-sm text-gray-400 line-clamp-2">
                      {{ producto.descripcion }}
                    </p>
                  </div>

                  <!-- Details -->
                  <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                      <span class="text-gray-500">Marca</span>
                      <span class="text-gray-300">{{ producto.marca?.nombre || 'Sin marca' }}</span>
                    </div>
                  </div>

                  <!-- Price & Action -->
                  <div class="flex items-center justify-between pt-3 border-t border-blue-500/20">
                    <div class="text-xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                      Bs {{ producto.precio_venta }}
                    </div>
                    <Link
                      :href="route('productos.show', producto.id_producto)"
                      class="px-3 py-1.5 bg-gradient-to-r from-blue-600 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-lg hover:shadow-blue-500/50 transition-all duration-300"
                    >
                      Ver
                    </Link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Clientes Recientes -->
        <div>
          <div class="bg-[#0f1f3a]/60 backdrop-blur-xl rounded-2xl border border-blue-500/20 p-6 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center space-x-3">
                <div class="p-2 rounded-xl bg-gradient-to-br from-cyan-500/20 to-teal-500/20 border border-cyan-500/30">
                  <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-white">Clientes Recientes</h3>
              </div>
              <Link
                :href="route('clientes.index')"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-500 text-white font-medium rounded-xl hover:shadow-lg hover:shadow-blue-500/50 transition-all duration-300 hover:-translate-y-0.5"
              >
                <span>Ver Todos</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </Link>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div v-for="cliente in clientes_recientes" :key="cliente.id_cliente"
                   class="group bg-[#0a1628]/60 rounded-xl p-4 border border-blue-500/20 hover:border-cyan-500/50 hover:shadow-lg hover:shadow-cyan-500/10 transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center shadow-lg shadow-cyan-500/20 group-hover:scale-110 transition-transform duration-300">
                    <span class="text-white font-bold text-sm">{{ getInitials(cliente.nombre) }}</span>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="text-white font-semibold truncate group-hover:text-cyan-400 transition-colors">
                      {{ cliente.nombre }}
                    </h4>
                    <p v-if="cliente.correo_electronico" class="text-sm text-gray-400 truncate">
                      {{ cliente.correo_electronico }}
                    </p>
                    <p v-else class="text-sm text-gray-500">Sin correo</p>
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
