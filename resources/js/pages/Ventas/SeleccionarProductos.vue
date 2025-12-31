<template>
    <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header con Buscador Más a la Derecha -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Seleccionar Productos</h1>
                        <p class="text-blue-300">Selecciona los productos que deseas vender</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Buscador más a la derecha -->
                        <div class="w-80">
                            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-3">
                                <div class="relative">
                                    <input
                                        v-model="filtros.texto"
                                        type="text"
                                        placeholder="Buscar por nombre, modelo, descripción, marca, categoría..."
                                        class="w-full px-3 py-2 pr-8 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Link
                            :href="route('ventas.index')"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a Ventas
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Grid de Productos -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div
                    v-for="producto in productosFiltrados"
                    :key="producto.id_producto"
                    class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300"
                    :class="{ 'ring-2 ring-green-500 bg-green-500/10': carrito.includes(producto.id_producto) }"
                >
                    <!-- Imagen del producto -->
                    <div class="aspect-square bg-gradient-to-br from-gray-800 to-gray-900 rounded-lg mb-4 flex items-center justify-center border border-gray-700 overflow-hidden">
                        <img
                            v-if="producto.img_url"
                            :src="producto.img_url"
                            :alt="producto.nombre || producto.modelo?.nombre"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="text-center p-4">
                            <div class="w-16 h-16 mx-auto mb-3 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400 font-medium">{{ (producto.marca?.nombre || 'Producto').charAt(0).toUpperCase() }}</p>
                            <p class="text-xs text-gray-500 mt-1">Sin imagen</p>
                        </div>
                    </div>

                    <!-- Información del producto -->
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-white">{{ (producto.marca?.nombre || '') + ' ' + (producto.modelo?.nombre || 'Sin nombre') }}</h3>
                        <p class="text-sm text-blue-300">&nbsp;</p>
                        <p class="text-xs text-gray-400">{{ producto.descripcion || 'Sin descripción' }}</p>
                        <p class="text-xs text-blue-400">{{ producto.categoria?.nombre || 'Sin categoría' }}</p>

                        <!-- Stock y Precio -->
                        <div class="flex justify-between items-center pt-2">
                            <div>
                                <span class="text-sm text-gray-300">Stock:</span>
                                <span class="ml-1 font-semibold" :class="producto.stock_disponible > 0 ? 'text-green-400' : 'text-red-400'">
                                    {{ producto.stock_disponible }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm text-gray-300">Precio:</span>
                                <span class="ml-1 font-semibold text-green-400">Bs {{ producto.precio_venta || '0.00' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Toggle Carrito -->
                    <div class="mt-4">
                        <button
                            @click="toggleCarrito(producto)"
                            :disabled="producto.stock_disponible <= 0"
                            :class="carrito.includes(producto.id_producto)
                                ? 'w-full px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-500 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center'
                                : 'w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-500 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center'"
                        >
                            <svg v-if="!carrito.includes(producto.id_producto)" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ carrito.includes(producto.id_producto) ? 'En el Carrito' : 'Añadir al Carrito' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensaje cuando no hay productos -->
            <div v-if="productosFiltrados.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">No se encontraron productos</h3>
                <p class="text-gray-400">Intenta ajustar los filtros de búsqueda</p>
            </div>
        </div>

        <!-- Carrito Flotante -->
        <div
            v-if="carrito.length > 0"
            class="fixed bottom-6 right-6 bg-black/90 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 shadow-2xl z-50"
        >
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Carrito de Venta</h3>
                <button
                    @click="limpiarCarrito"
                    class="text-red-400 hover:text-red-300 transition-colors duration-200"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-2 mb-4 max-h-40 overflow-y-auto">
                <div
                    v-for="productoId in carrito"
                    :key="productoId"
                    class="flex items-center justify-between text-sm"
                >
                    <span class="text-white">{{ obtenerProducto(productoId)?.nombre || 'Producto' }}</span>
                    <span class="text-blue-300">{{ obtenerProducto(productoId)?.stock_disponible || 0 }} disp.</span>
                </div>
            </div>

            <div class="border-t border-blue-500/30 pt-4">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-white font-semibold">Total productos:</span>
                    <span class="text-blue-400 font-bold">{{ carrito.length }}</span>
                </div>

                <button
                    @click="completarVenta"
                    class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors duration-200 flex items-center justify-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Completar Venta
                </button>
            </div>
        </div>
    </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Producto {
    id_producto: number
    nombre?: string
    descripcion?: string
    precio_venta?: number
    stock_disponible: number
    marca?: { nombre: string }
    modelo?: { nombre: string }
    categoria?: { nombre: string }
}

interface Marca {
    id_marca: number
    nombre: string
}

interface Categoria {
    id_categoria: number
    nombre: string
}

const props = defineProps<{
    productos: Producto[]
}>()

// Estado del carrito
const carrito = ref<number[]>([])

// Filtros de búsqueda
const filtros = ref({
    texto: ''
})

// Productos filtrados
const productosFiltrados = computed(() => {
    let productos = props.productos

    // Filtro por texto (búsqueda multicampo)
    if (filtros.value.texto) {
        const texto = filtros.value.texto.toLowerCase()
        productos = productos.filter(producto =>
            (producto.nombre?.toLowerCase().includes(texto) || false) ||
            (producto.modelo?.nombre?.toLowerCase().includes(texto) || false) ||
            (producto.descripcion?.toLowerCase().includes(texto) || false) ||
            (producto.marca?.nombre?.toLowerCase().includes(texto) || false) ||
            (producto.categoria?.nombre?.toLowerCase().includes(texto) || false)
        )
    }

    return productos
})

// Funciones del carrito
const toggleCarrito = (producto: Producto) => {
    const index = carrito.value.indexOf(producto.id_producto)
    if (index > -1) {
        // Si está en el carrito, lo quita
        carrito.value.splice(index, 1)
    } else {
        // Si no está en el carrito, lo agrega
        carrito.value.push(producto.id_producto)
    }
}

const obtenerProducto = (id: number) => {
    return props.productos.find(p => p.id_producto === id)
}

const limpiarCarrito = () => {
    carrito.value = []
}

const completarVenta = () => {
    // Guardar productos seleccionados en sessionStorage
    const productosSeleccionados = carrito.value.map(id => obtenerProducto(id)).filter(Boolean)
    sessionStorage.setItem('productos_seleccionados', JSON.stringify(productosSeleccionados))

    // Redirigir a la interfaz de venta
    router.visit(route('ventas.create'))
}
</script>
