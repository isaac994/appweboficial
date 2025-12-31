<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Reportes de Inventario</h1>
                        <p class="text-blue-300">Análisis y estadísticas del inventario</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="generarPDF"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Generar PDF
                        </button>
                        <Link
                            :href="route('inventario.index')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver al Inventario
                        </Link>
                        <Link
                            :href="route('inventario.movimientos')"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-cyan-500 hover:from-cyan-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Ver Movimientos
                        </Link>
                    </div>
                </div>

                <!-- Resumen General -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-600/20 to-cyan-500/20 border border-blue-500/30 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-blue-300 mb-1">Valor Total del Inventario</div>
                                <div class="text-3xl font-bold text-white">{{ formatCurrency(valor_total_inventario) }}</div>
                                <div class="text-xs text-gray-400 mt-1">Valor del stock actual en inventario</div>
                            </div>
                            <div class="bg-blue-500/20 p-4 rounded-full">
                                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-green-600/20 to-green-500/20 border border-green-500/30 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm text-green-300 mb-1">Stock Total de Productos</div>
                                <div class="text-3xl font-bold text-white">{{ total_stock }}</div>
                                <div class="text-xs text-gray-400 mt-1">unidades en inventario</div>
                            </div>
                            <div class="bg-green-500/20 p-4 rounded-full">
                                <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Productos Más Vendidos -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-8">
                    <div class="px-6 py-4 border-b border-blue-500/30">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Top 10 Productos Más Vendidos
                        </h3>
                        <p class="text-blue-300 text-sm mt-1">Productos ordenados por cantidad vendida</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Marca</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-blue-300 uppercase tracking-wider">Unidades Vendidas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr v-for="(producto, index) in productos_mas_vendidos" :key="index" class="hover:bg-blue-500/10 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-300">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-white">{{ producto.producto }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-300">{{ producto.marca }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 text-blue-400 border border-blue-500/50">
                                            {{ producto.total_vendido }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Productos con Bajo Stock -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-red-500/30 overflow-hidden">
                    <div class="px-6 py-4 border-b border-red-500/30 bg-red-500/10">
                        <h3 class="text-xl font-semibold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Productos con Bajo Stock
                        </h3>
                        <p class="text-red-300 text-sm mt-1">Productos con menos de 5 unidades en stock</p>
                    </div>
                    <div v-if="productos_bajo_stock.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Marca</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-blue-300 uppercase tracking-wider">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr v-for="producto in productos_bajo_stock" :key="producto.id" class="hover:bg-blue-500/10 transition-colors">
                                    <td class="px-6 py-4 text-sm font-medium text-white">{{ producto.producto }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-300">{{ producto.marca }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-500/20 text-red-400 border border-red-500/50">
                                            {{ producto.stock }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                producto.stock === 0 ? 'bg-red-500/20 text-red-400 border border-red-500/50' :
                                                'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50'
                                            ]"
                                        >
                                            {{ producto.stock === 0 ? 'Agotado' : 'Stock Bajo' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12">
                        <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-white mb-2">¡Todo bien!</h3>
                        <p class="text-blue-300">No hay productos con bajo stock en este momento.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar PDF -->
        <div v-if="showPDFModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col m-4">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
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
                        <span class="text-sm text-gray-600">{{ pdfFilename }}</span>
                    </div>
                    <button
                        @click="cerrarModalPDF"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Contenido del PDF -->
                <div class="flex-1 overflow-auto p-4">
                    <iframe
                        v-if="pdfData"
                        :src="'data:application/pdf;base64,' + pdfData"
                        class="w-full h-full min-h-[600px] border border-gray-300 rounded"
                        type="application/pdf"
                    ></iframe>
                    <div v-else class="flex items-center justify-center h-full">
                        <p class="text-gray-600">Generando PDF...</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
    valor_total_inventario: number;
    total_stock: number;
    productos_rentables: any[];
    productos_bajo_stock: any[];
    productos_mas_vendidos: any[];
}>();

// Variables para el modal de PDF
const showPDFModal = ref(false);
const pdfData = ref('');
const pdfFilename = ref('');

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2,
    }).format(value).replace('BOB', 'Bs');
};

const generarPDF = async () => {
    try {
        console.log('Generando PDF de reportes de inventario...');
        showPDFModal.value = true;
        pdfData.value = '';

        const response = await fetch(route('reportes.inventario.reportes.pdf'), {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            pdfData.value = data.pdf;
            pdfFilename.value = data.filename;
            console.log('PDF generado exitosamente');
        } else {
            throw new Error(data.message || 'Error al generar el PDF');
        }

    } catch (error: any) {
        console.error('Error generando PDF:', error);
        alert('Error al generar el PDF: ' + (error.message || 'Error desconocido'));
        showPDFModal.value = false;
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
</script>

