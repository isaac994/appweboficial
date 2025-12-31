<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Inventario</h1>
                        <p class="text-blue-300">Control de stock, entradas y salidas de productos</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="exportarPDF"
                            :disabled="cargandoPDF"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 disabled:from-gray-600 disabled:to-gray-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                        >
                            <svg v-if="cargandoPDF" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ cargandoPDF ? 'Generando PDF...' : 'Exportar PDF' }}
                        </button>
                        <Link
                            :href="route('inventario.movimientos')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Ver Movimientos
                        </Link>
                        <Link
                            :href="route('inventario.reportes')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Reportes
                        </Link>
                    </div>
                </div>

                <!-- Modal PDF Inventario -->
                <div v-if="showPDFModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-[95vw] h-[90vh] flex flex-col m-4">
                        <!-- Header del Modal -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-white">
                            <div class="flex items-center gap-3 flex-wrap">
                                <button
                                    @click="abrirNuevaPestana"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                    title="Abrir en nueva pestaña"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Abrir en nueva pestaña
                                </button>
                                <button
                                    @click="descargarPDF"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors"
                                    title="Guardar en PDF"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Guardar en PDF
                                </button>
                                <span class="text-sm text-gray-600 font-medium">{{ pdfFilename }}</span>
                            </div>
                            <button
                                @click="cerrarModalPDF"
                                class="text-gray-400 hover:text-gray-600 transition-colors ml-4"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Contenido del PDF -->
                        <div class="flex-1 overflow-hidden p-4 bg-gray-100">
                            <iframe
                                v-if="pdfData"
                                :src="'data:application/pdf;base64,' + pdfData"
                                class="w-full h-full border border-gray-300 rounded bg-white"
                                type="application/pdf"
                            ></iframe>
                            <div v-else class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                                    <p class="text-gray-600 text-lg">Generando PDF...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Búsqueda Multicampo -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-blue-300 mb-2">Buscar por Producto, Categoría, Marca o Modelo</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <input
                                    ref="searchInput"
                                    :value="filters.search"
                                    type="text"
                                    placeholder="Buscar por producto, categoría, marca o modelo..."
                                    class="w-full pl-10 pr-4 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    @input="handleSearchInput"
                                />
                            </div>
                        </div>

                        <!-- Fecha Final -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Fecha Final (Opcional)</label>
                            <input
                                v-model="filters.fecha_final"
                                type="date"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @change="search"
                            />
                        </div>

                        <!-- Stock Mínimo -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Stock Mínimo</label>
                            <input
                                v-model="filters.stock_min"
                                type="number"
                                min="0"
                                placeholder="0"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @input="debounceSearch"
                            />
                        </div>

                        <!-- Stock Máximo -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Stock Máximo</label>
                            <input
                                v-model="filters.stock_max"
                                type="number"
                                min="0"
                                placeholder="∞"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @input="debounceSearch"
                            />
                        </div>
                    </div>
                </div>


                <!-- Tabla de Inventario -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Categoría</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-blue-300 uppercase tracking-wider">Entradas</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-300 uppercase tracking-wider">Costo Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-blue-300 uppercase tracking-wider">Salidas</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-300 uppercase tracking-wider">Ingresos</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-300 uppercase tracking-wider">Ganancia Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-blue-300 uppercase tracking-wider">Stock</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-300 uppercase tracking-wider">Valor Stock</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr v-for="item in inventario" :key="item.id_producto" class="hover:bg-blue-500/10 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-white">{{ item.marca }} {{ item.modelo }}</div>
                                        <div class="text-xs text-gray-400 mt-1">{{ item.producto }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-300">{{ item.categoria }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/50">
                                            {{ item.total_entradas }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-300">
                                        {{ formatCurrency(item.costo_total_entradas) }}
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400 border border-red-500/50">
                                            {{ item.total_salidas }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-300">
                                        {{ formatCurrency(item.ingresos_totales) }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <span :class="item.ganancia_bruta >= 0 ? 'text-green-400' : 'text-red-400'" class="text-sm font-semibold">
                                            {{ formatCurrency(item.ganancia_bruta) }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                item.stock_actual > 10 ? 'bg-blue-500/20 text-blue-400 border border-blue-500/50' :
                                                item.stock_actual > 0 ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50' :
                                                'bg-red-500/20 text-red-400 border border-red-500/50'
                                            ]"
                                        >
                                            {{ item.stock_actual }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-medium text-white">
                                        {{ formatCurrency(item.valor_stock) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!inventario || inventario.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-white mb-2">No hay productos en el inventario</h3>
                        <p class="text-blue-300">Comienza registrando compras para agregar productos al inventario.</p>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="productos && productos.links && productos.links.length > 3" class="mt-6 flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button
                            v-if="productos.prev_page_url"
                            @click="router.get(productos.prev_page_url)"
                            class="relative inline-flex items-center px-4 py-2 border border-blue-500/50 text-sm font-medium rounded-md text-white bg-black/30 hover:bg-blue-600/20"
                        >
                            Anterior
                        </button>
                        <button
                            v-if="productos.next_page_url"
                            @click="router.get(productos.next_page_url)"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-blue-500/50 text-sm font-medium rounded-md text-white bg-black/30 hover:bg-blue-600/20"
                        >
                            Siguiente
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-blue-300">
                                Mostrando
                                <span class="font-medium">{{ productos.from || 0 }}</span>
                                a
                                <span class="font-medium">{{ productos.to || 0 }}</span>
                                de
                                <span class="font-medium">{{ productos.total || 0 }}</span>
                                resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <template v-for="(link, index) in productos.links" :key="index">
                                    <button
                                        v-if="link.url"
                                        @click="router.get(link.url)"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            link.active
                                                ? 'z-10 bg-blue-600 border-blue-500 text-white'
                                                : 'bg-black/30 border-blue-500/50 text-white hover:bg-blue-600/20'
                                        ]"
                                        v-html="link.label"
                                    ></button>
                                    <span
                                        v-else
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border border-blue-500/50 text-sm font-medium text-gray-400 bg-black/30',
                                        ]"
                                        v-html="link.label"
                                    ></span>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Resumen -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-8">
                    <div class="bg-gradient-to-r from-green-600/20 to-green-800/20 border border-green-500/30 rounded-lg p-6">
                        <div class="text-sm text-green-300 mb-1">Total Invertido</div>
                        <div class="text-2xl font-bold text-white">{{ formatCurrency(totalInvertido) }}</div>
                    </div>
                    <div class="bg-gradient-to-r from-blue-600/20 to-blue-800/20 border border-blue-500/30 rounded-lg p-6">
                        <div class="text-sm text-blue-300 mb-1">Total Ingresos</div>
                        <div class="text-2xl font-bold text-white">{{ formatCurrency(totalIngresos) }}</div>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-600/20 to-yellow-800/20 border border-yellow-500/30 rounded-lg p-6">
                        <div class="text-sm text-yellow-300 mb-1">Ganancia Total</div>
                        <div class="text-2xl font-bold text-white">{{ formatCurrency(totalGanancia) }}</div>
                    </div>
                    <div class="bg-gradient-to-r from-purple-600/20 to-purple-800/20 border border-purple-500/30 rounded-lg p-6">
                        <div class="text-sm text-purple-300 mb-1">Valor en Stock</div>
                        <div class="text-2xl font-bold text-white">{{ formatCurrency(valorTotalStock) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    inventario: any[];
    productos: any;
    categorias: any[];
    marcas: any[];
    modelos: any[];
    filters: {
        search: string;
        fecha_final: string;
        stock_min: string;
        stock_max: string;
    };
}>();

// Debug: Verificar datos recibidos
console.log('Inventario recibido:', props.inventario);
console.log('Total items:', props.inventario?.length);
if (props.inventario && props.inventario.length > 0) {
    console.log('Primer item completo:', JSON.stringify(props.inventario[0], null, 2));
    console.log('Stock del primer item:', props.inventario[0].stock_actual);
    console.log('Entradas del primer item:', props.inventario[0].total_entradas);
}

const filters = ref({
    search: props.filters.search || '',
    fecha_final: props.filters.fecha_final || '',
    stock_min: props.filters.stock_min || '',
    stock_max: props.filters.stock_max || '',
});

// Reset filtros a vacío si son los valores por defecto
if (filters.value.stock_min === '0') {
    filters.value.stock_min = '';
}

const searchInput = ref<HTMLInputElement | null>(null);
let searchTimeout: NodeJS.Timeout;

const handleSearchInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const cursorPosition = target.selectionStart || 0;
    
    // Actualizar el valor del filtro
    filters.value.search = target.value;
    
    // Restaurar la posición del cursor inmediatamente
    nextTick(() => {
        if (searchInput.value) {
            searchInput.value.setSelectionRange(cursorPosition, cursorPosition);
        }
    });
    
    // Ejecutar búsqueda con debounce
    debounceSearch();
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        search();
    }, 500);
};

const search = () => {
    router.get(route('inventario.index'), filters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: false,
        onFinish: () => {
            // Restaurar el foco después de la búsqueda
            nextTick(() => {
                if (searchInput.value) {
                    const cursorPos = searchInput.value.selectionStart || searchInput.value.value.length;
                    searchInput.value.focus();
                    searchInput.value.setSelectionRange(cursorPos, cursorPos);
                }
            });
        }
    });
};



// -------- PDF ---------
const showPDFModal = ref(false);
const pdfData = ref('');
const pdfFilename = ref('');
const cargandoPDF = ref(false);

const exportarPDF = async () => {
    cargandoPDF.value = true;
    showPDFModal.value = true;
    pdfData.value = '';

    try {
        // Obtener el token CSRF de la página de Inertia (más confiable)
        const page = usePage();
        let csrfToken = (page.props as any).csrf_token || 
                       document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            // Si no hay token, intentar recargar la página para obtener uno nuevo
            throw new Error('No se pudo obtener el token CSRF. Por favor, recarga la página.');
        }

        // Generar la URL de la ruta
        const url = route('inventario.index.pdf');
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin', // Importante: incluir cookies de sesión
            body: JSON.stringify({
                search: filters.value.search,
                fecha_final: filters.value.fecha_final,
                stock_min: filters.value.stock_min,
                stock_max: filters.value.stock_max,
            })
        });

        // Verificar si la respuesta es válida
        if (!response.ok) {
            // Si es error 419 (CSRF token mismatch), intentar recargar y obtener nuevo token
            if (response.status === 419) {
                // Intentar obtener un nuevo token recargando la página
                const newToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (newToken && newToken !== csrfToken) {
                    // Si hay un nuevo token, intentar de nuevo una vez
                    csrfToken = newToken;
                    const retryResponse = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            search: filters.value.search,
                            fecha_final: filters.value.fecha_final,
                            stock_min: filters.value.stock_min,
                            stock_max: filters.value.stock_max,
                        })
                    });
                    
                    if (retryResponse.ok) {
                        const retryData = await retryResponse.json();
                        if (retryData.success) {
                            pdfData.value = retryData.pdf;
                            pdfFilename.value = retryData.filename;
                            return;
                        }
                    }
                }
                throw new Error('Error de autenticación. Por favor, recarga la página e intenta nuevamente.');
            }
            
            const errorText = await response.text();
            let errorMessage = 'Error al generar el PDF';
            
            try {
                const errorData = JSON.parse(errorText);
                errorMessage = errorData.message || errorMessage;
            } catch (e) {
                errorMessage = `Error del servidor (${response.status})`;
            }
            
            throw new Error(errorMessage);
        }

        const data = await response.json();
        if (data.success) {
            pdfData.value = data.pdf;
            pdfFilename.value = data.filename;
        } else {
            throw new Error(data.message || 'Error al generar el PDF');
        }
    } catch (e: any) {
        console.error('Error al exportar PDF:', e);
        alert(e.message || 'Error de conexión al generar el PDF');
        showPDFModal.value = false;
    } finally {
        cargandoPDF.value = false;
    }
};

const cerrarModalPDF = () => {
    showPDFModal.value = false;
    pdfData.value = '';
    pdfFilename.value = '';
};

const abrirNuevaPestana = () => {
    if (pdfData.value) {
        const blob = new Blob([Uint8Array.from(atob(pdfData.value), c => c.charCodeAt(0))], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        window.open(url, '_blank');
    }
};

const descargarPDF = () => {
    if (pdfData.value) {
        const blob = new Blob([Uint8Array.from(atob(pdfData.value), c => c.charCodeAt(0))], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = pdfFilename.value || 'inventario.pdf';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
};
const formatCurrency = (value: number) => {
    // Validar que el valor sea un número válido
    if (value === null || value === undefined || isNaN(value)) {
        return 'Bs 0.00';
    }

    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    }).format(value).replace('BOB', 'Bs');
};

// Función auxiliar para sumar valores de forma segura
const safeSum = (items: any[], property: string): number => {
    return items.reduce((sum, item) => {
        const value = item[property];
        // Convertir a número y validar que sea un número válido
        const numValue = parseFloat(value) || 0;
        return sum + numValue;
    }, 0);
};

// Cálculos de totales con validación
const totalInvertido = computed(() => {
    return safeSum(props.inventario, 'costo_total_entradas');
});

const totalIngresos = computed(() => {
    return safeSum(props.inventario, 'ingresos_totales');
});

const totalGanancia = computed(() => {
    return safeSum(props.inventario, 'ganancia_bruta');
});

const valorTotalStock = computed(() => {
    return safeSum(props.inventario, 'valor_stock');
});

// Productos con bajo stock (menos de 5 unidades)
</script>

