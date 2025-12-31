<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">Movimientos de Inventario</h1>
                    <p class="text-blue-300">Historial de entradas y salidas de productos</p>
                </div>

                <!-- Filtros -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Tipo de Movimiento -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Tipo de Movimiento</label>
                            <select
                                v-model="filters.tipo"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                @change="search"
                            >
                                <option value="">Todos</option>
                                <option value="entrada">Entradas</option>
                                <option value="salida">Salidas</option>
                            </select>
                        </div>

                        <!-- Fecha Hasta -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Hasta</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="filters.fecha_hasta"
                                    type="date"
                                    class="flex-1 px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    @change="search"
                                />
                                <button
                                    @click="limpiarFecha"
                                    class="px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                                    title="Limpiar filtro de fecha"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex flex-wrap gap-3 items-center">
                        <button
                            @click="exportarPDF"
                            :disabled="cargandoPDF"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 disabled:from-gray-600 disabled:to-gray-700 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <svg v-if="cargandoPDF" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ cargandoPDF ? 'Generando...' : 'Generar PDF' }}
                        </button>
                        <Link
                            :href="route('inventario.index')"
                            class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Volver al inventario
                        </Link>
                        <Link
                            :href="route('inventario.reportes')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Reportes
                        </Link>
                    </div>
                </div>

                <!-- Tabla de Movimientos -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Marca/Cat.</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-blue-300 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-300 uppercase tracking-wider">Precio Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-300 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Documento</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr v-for="movimiento in props.movimientos" :key="movimiento.id" class="hover:bg-blue-500/10 transition-colors">
                                    <td class="px-4 py-4 text-sm text-gray-300">
                                        {{ formatDate(movimiento.fecha) }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                movimiento.tipo_class === 'entrada' ?
                                                'bg-green-500/20 text-green-400 border border-green-500/50' :
                                                'bg-red-500/20 text-red-400 border border-red-500/50'
                                            ]"
                                        >
                                            {{ movimiento.tipo }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-white">{{ movimiento.producto }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-300">{{ movimiento.marca }}</div>
                                        <div class="text-xs text-gray-400">{{ movimiento.categoria }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ movimiento.cantidad }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-300">
                                        {{ formatCurrency(movimiento.precio_unitario) }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <span class="text-sm font-semibold text-white">
                                            {{ formatCurrency(movimiento.total) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-300">{{ movimiento.documento }}</div>
                                        <div v-if="movimiento.proveedor" class="text-xs text-gray-400">{{ movimiento.proveedor }}</div>
                                        <div v-if="movimiento.cliente" class="text-xs text-gray-400">{{ movimiento.cliente }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-300">
                                        {{ movimiento.usuario }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!props.movimientos || props.movimientos.length === 0" class="text-center py-12">
                        <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-white mb-2">No hay movimientos</h3>
                        <p class="text-blue-300">No se encontraron movimientos con los filtros aplicados.</p>
                    </div>
                </div>

        <!-- Modal para mostrar PDF -->
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

                <!-- Resumen de Movimientos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="bg-gradient-to-r from-green-600/20 to-green-800/20 border border-green-500/30 rounded-lg p-6">
                        <div class="text-sm text-green-300 mb-1">Total Entradas</div>
                        <div class="text-2xl font-bold text-white">{{ totalEntradas }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ formatCurrency(valorEntradas) }}</div>
                    </div>
                    <div class="bg-gradient-to-r from-red-600/20 to-red-800/20 border border-red-500/30 rounded-lg p-6">
                        <div class="text-sm text-red-300 mb-1">Total Salidas</div>
                        <div class="text-2xl font-bold text-white">{{ totalSalidas }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ formatCurrency(valorSalidas) }}</div>
                    </div>
                    <div class="bg-gradient-to-r from-blue-600/20 to-blue-800/20 border border-blue-500/30 rounded-lg p-6">
                        <div class="text-sm text-blue-300 mb-1">Diferencia</div>
                        <div class="text-2xl font-bold text-white">{{ totalEntradas - totalSalidas }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ formatCurrency(valorEntradas - valorSalidas) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    movimientos: any[];
    filters: {
        search: string;
        tipo: string;
        fecha_hasta: string;
    };
}>();

const filters = ref({
    search: props.filters.search || '',
    tipo: props.filters.tipo || '',
    fecha_hasta: props.filters.fecha_hasta || '',
});

// Sincronizar filters con props cuando cambien
watch(() => props.filters, (newFilters) => {
    filters.value = {
        search: newFilters.search || '',
        tipo: newFilters.tipo || '',
        fecha_hasta: newFilters.fecha_hasta || '',
    };
}, { deep: true });

const search = () => {
    const queryParams: Record<string, string> = {};

    if (filters.value.search) queryParams.search = filters.value.search;
    if (filters.value.tipo) queryParams.tipo = filters.value.tipo;
    if (filters.value.fecha_hasta) queryParams.fecha_hasta = filters.value.fecha_hasta;

    router.get(route('inventario.movimientos'), queryParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['movimientos', 'filters'],
    });
};

const limpiarFecha = () => {
    filters.value.fecha_hasta = '';
    search();
};

// Variables para el modal de PDF
const showPDFModal = ref(false);
const pdfData = ref('');
const pdfFilename = ref('');
const cargandoPDF = ref(false);

const exportarPDF = async () => {
    cargandoPDF.value = true;
    showPDFModal.value = true;
    pdfData.value = '';

    try {
        // Preparar los filtros actuales
        const queryParams: Record<string, string> = {};
        if (filters.value.search) queryParams.search = filters.value.search;
        if (filters.value.tipo) queryParams.tipo = filters.value.tipo;
        if (filters.value.fecha_hasta) queryParams.fecha_hasta = filters.value.fecha_hasta;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const response = await fetch(route('inventario.movimientos.pdf', {}, false), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify(queryParams)
        });

        const result = await response.json();

        if (result.success) {
            pdfData.value = result.pdf;
            pdfFilename.value = result.filename;
        } else {
            throw new Error(result.message || 'Error al generar el PDF');
        }
    } catch (error: any) {
        console.error('Error al exportar PDF:', error);
        alert('Error al generar el PDF: ' + (error.message || 'Error desconocido'));
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
        a.download = pdfFilename.value;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
};


const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    }).format(value).replace('BOB', 'Bs');
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-BO', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    }).format(date);
};

// Cálculos de totales
const totalEntradas = computed(() => {
    return props.movimientos.filter(m => m.tipo_class === 'entrada').reduce((sum, m) => sum + m.cantidad, 0);
});

const totalSalidas = computed(() => {
    return props.movimientos.filter(m => m.tipo_class === 'salida').reduce((sum, m) => sum + m.cantidad, 0);
});

const valorEntradas = computed(() => {
    return props.movimientos.filter(m => m.tipo_class === 'entrada').reduce((sum, m) => sum + m.total, 0);
});

const valorSalidas = computed(() => {
    return props.movimientos.filter(m => m.tipo_class === 'salida').reduce((sum, m) => sum + m.total, 0);
});
</script>

