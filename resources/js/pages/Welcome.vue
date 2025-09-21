<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

interface Producto {
  id_producto: number
  nombre: string
  descripcion?: string
  precio_venta: number
  estado: string
  estado_disponible: string
  img_url?: string
  categoria?: {
    id_categoria: number
    nombre: string
  }
  marca?: {
    id_marca: number
    nombre: string
  }
}

interface Categoria {
  id_categoria: number
  nombre: string
}

interface Marca {
  id_marca: number
  nombre: string
}

const props = defineProps<{
  productos: {
    data: Producto[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  categorias: Categoria[]
  marcas: Marca[]
  filters: {
    search?: string
    categoria?: string
    marca?: string
    estado?: string
    sort?: string
    order?: string
  }
}>()

// Filtros reactivos
const search = ref(props.filters.search || '')
const categoria = ref(props.filters.categoria || '')
const marca = ref(props.filters.marca || '')
const estado = ref(props.filters.estado || '')
const sort = ref(props.filters.sort || 'nombre')
const order = ref(props.filters.order || 'asc')
const isLoading = ref(false)

// Función para aplicar filtros automáticamente
const applyFilters = () => {
  isLoading.value = true
  router.get('/', {
    search: search.value,
    categoria: categoria.value,
    marca: marca.value,
    estado: estado.value,
    sort: sort.value,
    order: order.value
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false
    }
  })
}

// Función para limpiar filtros
const clearFilters = () => {
  search.value = ''
  categoria.value = ''
  marca.value = ''
  estado.value = ''
  sort.value = 'nombre'
  order.value = 'asc'
  applyFilters()
}

// Función para cambiar ordenamiento
const changeSort = (newSort: string) => {
  if (sort.value === newSort) {
    order.value = order.value === 'asc' ? 'desc' : 'asc'
  } else {
    sort.value = newSort
    order.value = 'asc'
  }
  applyFilters()
}

// Watchers para aplicar filtros automáticamente
watch(search, () => {
  applyFilters()
}, { debounce: 300 })

watch(categoria, () => {
  applyFilters()
})

watch(marca, () => {
  applyFilters()
})

watch(estado, () => {
  applyFilters()
})

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.style.display = 'none'
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-purple-500/30', 'to-pink-500/30')
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-purple-600/20 via-pink-600/20 to-indigo-600/20 border-b border-purple-500/30">
      <div class="absolute inset-0 bg-black/10"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Header -->
        <header class="mb-12">
          <div class="flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-gradient-to-r from-purple-600 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <h1 class="text-3xl font-bold text-white mb-1">
                  <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    Celulares y Accesorios
                  </span>
                </h1>
                <p class="text-purple-300 text-lg font-medium">Cochabamba</p>
              </div>
            </div>

            <!-- Navigation -->
            <nav class="flex items-center space-x-4">
              <Link
                :href="route('login')"
                class="px-6 py-3 text-gray-300 hover:text-white transition-colors duration-200 font-medium"
              >
                Acceso
              </Link>
              <Link
                :href="route('register')"
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl"
              >
                Registro
              </Link>
            </nav>
          </div>
        </header>

        <!-- Hero Content -->
        <div class="text-center">
          <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
            <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
              Tecnología de Vanguardia
            </span>
          </h2>
          <p class="text-lg md:text-xl text-purple-200 max-w-2xl mx-auto leading-relaxed">
            Descubre la mejor selección de celulares, accesorios y tecnología en Cochabamba
          </p>
        </div>
      </div>
    </section>

    <!-- Filters Section -->
    <section class="py-6 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Búsqueda -->
            <div>
              <div class="relative">
                <input
                  v-model="search"
                  type="text"
                  placeholder="Buscar productos..."
                  class="w-full pl-10 pr-4 py-2.5 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                />
                <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>

            <!-- Categoría -->
            <div>
              <select
                v-model="categoria"
                class="w-full px-3 py-2.5 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
              >
                <option value="">Todas las categorías</option>
                <option v-for="cat in categorias" :key="cat.id_categoria" :value="cat.id_categoria">
                  {{ cat.nombre }}
                </option>
              </select>
            </div>

            <!-- Marca -->
            <div>
              <select
                v-model="marca"
                class="w-full px-3 py-2.5 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
              >
                <option value="">Todas las marcas</option>
                <option v-for="m in marcas" :key="m.id_marca" :value="m.id_marca">
                  {{ m.nombre }}
                </option>
              </select>
            </div>

            <!-- Estado -->
            <div>
              <select
                v-model="estado"
                class="w-full px-3 py-2.5 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
              >
                <option value="">Todos los estados</option>
                <option value="disponible">Disponible</option>
                <option value="agotado">Agotado</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Products Section -->
    <section class="py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <!-- Header simplificado -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
          <div class="mb-2 sm:mb-0">
            <h2 class="text-2xl font-bold text-white">
              <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Nuestros Productos
              </span>
            </h2>
            <p class="text-gray-300 text-sm">
              {{ productos.data.length }} de {{ productos.total }} productos disponibles
            </p>
          </div>
        </div>

        <!-- Loading Indicator -->
        <div v-if="isLoading" class="flex justify-center items-center py-8">
          <div class="flex items-center space-x-2">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-500"></div>
            <span class="text-purple-300">Aplicando filtros...</span>
          </div>
        </div>

        <!-- Products Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="producto in productos.data"
            :key="producto.id_producto"
            class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <!-- Product Image -->
            <div class="aspect-square bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center relative">
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

              <!-- Estado badge -->
              <div class="absolute top-2 right-2">
                <span
                  :class="producto.estado_disponible === 'disponible' ? 'bg-green-500' : 'bg-red-500'"
                  class="px-2 py-1 rounded-full text-xs font-medium text-white"
                >
                  {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                </span>
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

        <!-- Pagination -->
        <div v-if="!isLoading && productos.last_page > 1" class="mt-8 flex justify-center">
          <div class="flex items-center space-x-2">
            <Link
              v-if="productos.current_page > 1"
              :href="route('home', { ...filters, page: productos.current_page - 1 })"
              class="px-3 py-2 bg-black/30 border border-purple-500/50 rounded-lg text-white hover:bg-purple-500/20 transition-colors"
            >
              Anterior
            </Link>

            <span class="px-3 py-2 text-gray-300">
              Página {{ productos.current_page }} de {{ productos.last_page }}
            </span>

            <Link
              v-if="productos.current_page < productos.last_page"
              :href="route('home', { ...filters, page: productos.current_page + 1 })"
              class="px-3 py-2 bg-black/30 border border-purple-500/50 rounded-lg text-white hover:bg-purple-500/20 transition-colors"
            >
              Siguiente
            </Link>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!isLoading && productos.data.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 text-purple-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h3 class="text-xl font-semibold text-white mb-2">No se encontraron productos</h3>
          <p class="text-purple-300 mb-4">Intenta ajustar los filtros de búsqueda</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black/20 backdrop-blur-sm border-t border-purple-500/30 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <p class="text-gray-300">
            © 2024 Celulares y Accesorios Cochabamba. Todos los derechos reservados.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>
