<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, onMounted, onUnmounted } from 'vue'

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
  modelo?: {
    id_modelo: number
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

const search = ref(props.filters.search || '')
const categoria = ref(props.filters.categoria || '')
const marca = ref(props.filters.marca || '')
const estado = ref(props.filters.estado || '')
const isLoading = ref(false)

// Carrusel state
const currentSlide = ref(0)
const carruselImages = [
  '/images/carrusel1.png',
  '/images/carrusel2.jpg',
  '/images/carrusel3.jpg'
]

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % carruselImages.length
  restartAutoPlay()
}

const prevSlide = () => {
  currentSlide.value = currentSlide.value === 0 ? carruselImages.length - 1 : currentSlide.value - 1
  restartAutoPlay()
}

const goToSlide = (index: number) => {
  currentSlide.value = index
  restartAutoPlay()
}

const restartAutoPlay = () => {
  stopAutoPlay()
  startAutoPlay()
}

// Auto-play functionality
let autoPlayInterval: NodeJS.Timeout | null = null

const startAutoPlay = () => {
  autoPlayInterval = setInterval(() => {
    nextSlide()
  }, 5000) // Change slide every 5 seconds
}

const stopAutoPlay = () => {
  if (autoPlayInterval) {
    clearInterval(autoPlayInterval)
    autoPlayInterval = null
  }
}

onMounted(() => {
  startAutoPlay()
})

onUnmounted(() => {
  stopAutoPlay()
})


const applyFilters = () => {
  isLoading.value = true
  router.get('/', {
    search: search.value,
    categoria: categoria.value,
    marca: marca.value,
    estado: estado.value
  }, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      isLoading.value = false
      // Auto-scroll al catálogo cuando se filtra
      if (search.value.trim()) {
        const catalogSection = document.getElementById('catalogo')
        if (catalogSection) {
          catalogSection.scrollIntoView({ behavior: 'smooth' })
        }
      }
    }
  })
}

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
  img.parentElement?.classList.add('bg-gradient-to-br', 'from-blue-500/30', 'to-cyan-500/30')
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
    <Head title="Celulares y Accesorios - Independencia" />

    <nav class="fixed top-0 left-0 right-0 z-50 bg-[#0a1628]/80 backdrop-blur-xl border-b border-blue-500/10">
      <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
            <span class="text-white font-semibold text-lg">Celulares Store</span>
          </div>

          <!-- Search Bar -->
          <div class="flex-1 max-w-md mx-8">
            <div class="relative">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por modelo, descripción, marca, categoría, estado..."
                class="w-full pl-12 pr-4 py-3 bg-[#0a1628] border border-blue-500/30 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-blue-500 transition-colors"
              />
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>

          <div class="flex items-center space-x-4">
            <a href="tel:+59167473050" class="text-white font-medium flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </svg>
              <span>+591 67473050</span>
            </a>
            <Link
              :href="route('login')"
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-500 text-white rounded-lg font-medium hover:shadow-lg hover:shadow-blue-500/50 transition-all duration-300"
            >
              Acceder
            </Link>
          </div>
        </div>
      </div>
    </nav>

    <section id="inicio" class="relative pt-32 pb-20 px-6 overflow-hidden min-h-screen flex items-center">
      <div class="absolute inset-0 bg-gradient-to-br from-[#0a1628] via-[#0d2137] to-[#0a1628]"></div>

      <div class="max-w-7xl mx-auto w-full relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div class="space-y-8">
            <div class="space-y-6">
              <h1 class="text-6xl lg:text-7xl font-bold leading-tight">
                <span class="text-white block">Celulares y</span>
                <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent block">
                  Accesorios
                </span>
              </h1>

              <p class="text-xl text-gray-400 leading-relaxed max-w-xl">
                La mejor tecnología móvil al alcance de tu mano en Independencia, Cochabamba
              </p>
            </div>

            <div>
              <a
                href="#catalogo"
                class="inline-flex items-center space-x-3 px-8 py-4 bg-gradient-to-r from-[#2563eb] to-[#3b82f6] text-white text-lg font-semibold rounded-xl hover:shadow-2xl hover:shadow-blue-500/50 transition-all duration-300"
              >
                <span>Ver Catálogo</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
              </a>
            </div>

          </div>

          <div class="relative flex items-center justify-center -mt-24 -ml-16">
            <div class="relative w-full max-w-2xl">
              <div
                class="relative overflow-hidden rounded-2xl"
                @mouseenter="stopAutoPlay"
                @mouseleave="startAutoPlay"
              >
                <div
                  class="flex transition-transform duration-500 ease-in-out"
                  :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
                >
                  <div
                    v-for="(image, index) in carruselImages"
                    :key="index"
                    class="w-full flex-shrink-0"
                  >
                    <img
                      :src="image"
                      :alt="`Celulares y Accesorios ${index + 1}`"
                      class="w-full h-auto object-cover"
                    />
                  </div>
                </div>
              </div>

              <button
                @click="prevSlide"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all duration-300 backdrop-blur-sm"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
              </button>

              <button
                @click="nextSlide"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-all duration-300 backdrop-blur-sm"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                <button
                  v-for="(image, index) in carruselImages"
                  :key="index"
                  @click="goToSlide(index)"
                  :class="currentSlide === index
                    ? 'bg-white'
                    : 'bg-white/50 hover:bg-white/70'"
                  class="w-3 h-3 rounded-full transition-all duration-300"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <section id="catalogo" class="py-16 px-6">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-4xl font-bold mb-4">
            <span class="text-white">Nuestro </span>
            <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">Catálogo</span>
          </h2>
          <p class="text-gray-400 text-lg">
            {{ productos.data.length }} de {{ productos.total }} productos disponibles
          </p>
        </div>

        <div v-if="isLoading" class="flex justify-center items-center py-20">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin"></div>
            <span class="text-gray-400">Cargando productos...</span>
          </div>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="producto in productos.data"
            :key="producto.id_producto"
            class="group bg-[#0f1f3a]/40 backdrop-blur-xl border border-blue-500/20 rounded-2xl overflow-hidden hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/20 transition-all duration-500 hover:-translate-y-2"
          >
            <div class="relative aspect-square bg-gradient-to-br from-blue-500/10 to-cyan-500/10 overflow-hidden">
              <img
                v-if="producto.img_url"
                :src="producto.img_url + '?v=' + Date.now()"
                :alt="producto.nombre"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                @error="handleImageError"
              />
              <div v-else class="w-full h-full bg-gradient-to-br from-blue-500/20 to-cyan-500/20 flex items-center justify-center">
                <svg class="w-20 h-20 text-blue-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
              </div>

              <div class="absolute top-3 right-3">
                <span
                  :class="producto.estado_disponible === 'disponible'
                    ? 'bg-gradient-to-r from-green-500 to-emerald-500'
                    : 'bg-gradient-to-r from-red-500 to-pink-500'"
                  class="px-3 py-1 rounded-full text-xs font-bold text-white backdrop-blur-xl"
                >
                  {{ producto.estado_disponible === 'disponible' ? 'Disponible' : 'Agotado' }}
                </span>
              </div>

              <div class="absolute inset-0 bg-gradient-to-t from-[#0a1628] via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </div>

            <div class="p-6 space-y-4">
              <div>
                <h3 class="text-lg font-semibold text-white mb-2 line-clamp-2 group-hover:text-blue-400 transition-colors">
                  {{ producto.categoria?.nombre?.toLowerCase().includes('accesorio simple')
                     ? (producto.descripcion || 'Sin descripción')
                     : ((producto.marca?.nombre || '') + ' ' + (producto.modelo?.nombre || producto.nombre || 'Sin modelo')).trim() }}
                </h3>
                <p v-if="!producto.categoria?.nombre?.toLowerCase().includes('accesorio simple') && producto.descripcion" class="text-sm text-gray-400 line-clamp-2">
                  {{ producto.descripcion }}
                </p>
              </div>

              <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-500">Marca</span>
                  <span class="text-gray-300">{{ producto.marca?.nombre || 'Sin marca' }}</span>
                </div>
              </div>

              <div class="flex items-center justify-between pt-4 border-t border-blue-500/20">
                <div class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                  Bs {{ producto.precio_venta }}
                </div>
                <Link
                  :href="route('login')"
                  class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-500 text-white text-sm font-medium rounded-lg hover:shadow-lg hover:shadow-blue-500/50 transition-all duration-300"
                >
                  Ver más
                </Link>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!isLoading && productos.data.length === 0" class="text-center py-20">
          <div class="w-24 h-24 bg-blue-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 00-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-white mb-2">No se encontraron productos</h3>
          <p class="text-gray-400 mb-6">Intenta ajustar los filtros de búsqueda</p>
        </div>

        <div v-if="!isLoading && productos.last_page > 1" class="mt-12 flex justify-center">
          <div class="bg-[#0f1f3a]/60 backdrop-blur-xl border border-blue-500/20 rounded-xl p-2 flex items-center space-x-2">
            <Link
              v-if="productos.current_page > 1"
              :href="route('home', { ...filters, page: productos.current_page - 1 })"
              class="px-4 py-2 text-white hover:bg-blue-500/20 rounded-lg transition-colors"
            >
              Anterior
            </Link>
            <div class="px-4 py-2 text-gray-400">
              Página {{ productos.current_page }} de {{ productos.last_page }}
            </div>
            <Link
              v-if="productos.current_page < productos.last_page"
              :href="route('home', { ...filters, page: productos.current_page + 1 })"
              class="px-4 py-2 text-white hover:bg-blue-500/20 rounded-lg transition-colors"
            >
              Siguiente
            </Link>
          </div>
        </div>
      </div>
    </section>

    <footer class="py-12 px-6 border-t border-blue-500/10">
      <div class="max-w-7xl mx-auto">
        <div class="text-center space-y-4">
          <div class="flex items-center justify-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-400 rounded-xl flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
            </div>
            <span class="text-white font-semibold text-xl">Celulares y Accesorios</span>
          </div>
          <p class="text-gray-400">Independencia, Cochabamba - Bolivia</p>
          <p class="text-gray-500 text-sm">© 2025 Todos los derechos reservados</p>
        </div>
      </div>
    </footer>
  </div>
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
