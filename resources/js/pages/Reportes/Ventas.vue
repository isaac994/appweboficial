<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Reporte de Ventas</h1>
                        <p class="text-blue-300 text-sm">Análisis detallado de las ventas realizadas</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Filtros de Búsqueda</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <!-- Fecha inicial -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Fecha Inicial</label>
                            <input
                                type="date"
                                v-model="filtros.fecha_inicio"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Fecha final -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Fecha Final</label>
                            <input
                                type="date"
                                v-model="filtros.fecha_final"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Usuario -->
                        <div>
                            <label class="block text-sm font-medium text-blue-300 mb-2">Usuario</label>
                            <select
                                v-model="filtros.usuario"
                                class="w-full px-3 py-2 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="todos">Todos los usuarios</option>
                                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                    {{ usuario.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-end space-x-2">
                            <button
                                @click="generarPDF"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200"
                            >
                                Generar PDF
                            </button>
                        </div>
                    </div>

                    <!-- Categorías -->
                    <div>
                        <label class="block text-sm font-medium text-blue-300 mb-3">Categorías:</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            <div class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    id="todos"
                                    value="todos"
                                    v-model="seleccionados"
                                    @change="toggleTodos"
                                    class="w-4 h-4 text-green-600 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                                />
                                <label for="todos" class="text-sm text-white font-medium">Todos</label>
                            </div>
                            <div v-for="categoria in categorias" :key="categoria.id_categoria" class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    :id="categoria.id_categoria"
                                    :value="categoria.id_categoria"
                                    v-model="seleccionados"
                                    class="w-4 h-4 text-green-600 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                                />
                                <label :for="categoria.id_categoria" class="text-sm text-white">{{ categoria.nombre }}</label>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Mensaje cuando no hay ventas -->
                <div v-if="!ventas.length" class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-12 text-center">
                    <div class="text-gray-400 text-lg mb-2">No se encontraron ventas</div>
                    <div class="text-blue-300 text-sm">Intenta ajustar los filtros de búsqueda</div>
                </div>
            </div>
        </div>

        <!-- Modal para mostrar PDF -->
        <div v-if="showPDFModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-11/12 h-5/6 flex flex-col">
                <!-- Header del modal -->
                <div class="flex items-center justify-between p-4 border-b">
                    <div class="flex items-center space-x-4">
                        <button
                            @click="abrirNuevaPestana"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm"
                        >
                            Abrir nueva pestaña
                        </button>
                        <button
                            @click="descargarPDF"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
                        >
                            Guardar en PDF
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">{{ pdfFilename }}</span>
                        <button
                            @click="cerrarModalPDF"
                            class="text-gray-500 hover:text-gray-700 text-xl font-bold"
                        >
                            ×
                        </button>
                    </div>
                </div>

                <!-- Contenido del PDF -->
                <div class="flex-1 overflow-hidden">
                    <iframe
                        v-if="pdfData"
                        :src="'data:application/pdf;base64,' + pdfData"
                        class="w-full h-full border-0"
                        type="application/pdf"
                    ></iframe>
                    <div v-else class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
                            <p class="text-gray-600">Generando PDF...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Cliente {
    id_cliente: number;
    nombre: string;
    telefono?: string;
}

interface Categoria {
    id_categoria: number;
    nombre: string;
}

interface DetalleVenta {
    id_detalle_venta: number;
    cantidad: number;
    precio_unitario: number;
    subtotal: number;
    producto?: {
        categoria?: Categoria;
    };
}

interface Venta {
    id_venta: number;
    fecha: string;
    total: number;
    cliente: Cliente;
    detalles: DetalleVenta[];
}

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
    ventas: Venta[];
    usuarios: User[];
    categorias: Categoria[];
    filtros: {
        fecha_inicio: string;
        fecha_final: string;
        usuario: string;
        categorias: number[];
    };
}>();

// Función para obtener el primer día del mes actual
const getPrimerDiaMes = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    return `${year}-${month}-01`;
};

// Función para obtener el último día del mes actual
const getUltimoDiaMes = () => {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const lastDay = new Date(year, month, 0).getDate();
    return `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
};

// Inicializar filtros con fechas por defecto si no vienen en props
const filtros = ref({
    fecha_inicio: props.filtros?.fecha_inicio || getPrimerDiaMes(),
    fecha_final: props.filtros?.fecha_final || getUltimoDiaMes(),
    usuario: props.filtros?.usuario || 'todos',
    categorias: props.filtros?.categorias || []
});

// Inicializar con todas las categorías marcadas por defecto
const todasLasCategorias = props.categorias.map(cat => cat.id_categoria);
const categoriasPorDefecto = props.filtros.categorias.length ? props.filtros.categorias : ['todos', ...todasLasCategorias];
const seleccionados = ref(categoriasPorDefecto);

// Variables para el modal de PDF
const showPDFModal = ref(false);
const pdfData = ref('');
const pdfFilename = ref('');


const generarPDF = async () => {
    try {
        console.log('Generando PDF...');
        showPDFModal.value = true;
        pdfData.value = '';

        // Construir URL con parámetros
        const params = new URLSearchParams({
            fecha_inicio: filtros.value.fecha_inicio,
            fecha_final: filtros.value.fecha_final,
            usuario: filtros.value.usuario,
            categorias: JSON.stringify(seleccionados.value)
        });

        const response = await fetch(`${route('reportes.ventas.pdf.direct')}?${params}`, {
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

    } catch (error) {
        console.error('Error generando PDF:', error);
        alert('Error al generar el PDF: ' + error.message);
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


const toggleTodos = () => {
    if (seleccionados.value.includes('todos')) {
        seleccionados.value = ['todos'];
    } else {
        seleccionados.value = [];
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'Sin fecha';

    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatCurrency = (amount: number) => {
    return amount.toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};
</script>
