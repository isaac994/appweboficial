<template>
    <AppLayout title="Compras">
        <template #header>
            <Heading>Gestión de Compras</Heading>
        </template>

        <div class="space-y-6">
            <!-- Filtro de Búsqueda -->
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-white mb-2">Filtros de Búsqueda</h3>
                    <p class="text-purple-300 text-sm">El buscador permite filtrar por nombre del proveedor o ID de la compra</p>
                </div>
                <div class="flex justify-start">
                    <div class="relative w-full max-w-lg">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Buscar por nombre de proveedor o ID de compra..."
                            class="w-full pl-10 pr-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            @input="debounceSearch"
                            title="Busca por nombre del proveedor o ID de la compra"
                        />
                    </div>
                </div>
            </div>

            <!-- Tabla de Compras -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Lista de Compras</CardTitle>
                        <CardDescription>
                            Gestiona todas las compras realizadas a proveedores
                        </CardDescription>
                    </div>
                    <Button @click="router.visit(route('compras.create'))">
                        Nueva Compra
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="compras.data && compras.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">ID</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Proveedor</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Fecha</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Total</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Usuario</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="compra in compras.data"
                                    :key="compra.id_compra"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4 text-gray-900 font-mono">
                                        #{{ compra.id_compra }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900">
                                            {{ compra.proveedor?.nombre }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ formatDate(compra.fecha) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-green-600">
                                            {{ formatCurrency(compra.total) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ compra.usuario?.name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <Button
                                                @click="router.visit(route('compras.show', compra.id_compra))"
                                                variant="ghost"
                                                size="sm"
                                                class="p-2"
                                                title="Ver detalles"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </Button>
                                            <Button
                                                @click="router.visit(route('compras.edit', compra.id_compra))"
                                                variant="ghost"
                                                size="sm"
                                                class="p-2"
                                                title="Editar compra"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </Button>
                                            <Button
                                                @click="imprimirRecibo(compra)"
                                                variant="ghost"
                                                size="sm"
                                                class="p-2"
                                                title="Imprimir recibo"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                </svg>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje cuando no hay compras -->
                    <div v-else class="text-center py-8 text-gray-500">
                        <p>No hay compras registradas</p>
                        <p class="text-sm">Haga clic en "Nueva Compra" para comenzar</p>
                    </div>

                    <!-- Paginación -->
                    <div v-if="compras.links && compras.links.length > 3" class="mt-6">
                        <nav class="flex justify-center">
                            <div class="flex space-x-1">
                                <template v-for="(link, key) in compras.links" :key="key">
                                    <!-- Enlaces habilitados -->
                                    <Link
                                        v-if="link.url !== null"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                                        ]"
                                        v-html="link.label"
                                    />
                                    <!-- Enlaces deshabilitados -->
                                    <span
                                        v-else
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md text-gray-400 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Visor de PDF Modal -->
        <div v-if="mostrarPdf" class="fixed inset-0 z-50 overflow-hidden">
            <!-- Overlay de fondo -->
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

            <!-- Modal del PDF -->
            <div class="relative flex items-center justify-center min-h-screen p-4">
                <div class="relative w-full max-w-5xl h-[95vh] bg-white rounded-xl shadow-2xl overflow-hidden">
                    <!-- Header del modal con controles profesionales -->
                    <div class="bg-white border-b">
                        <!-- Barra superior con botones principales -->
                        <div class="flex items-center justify-between p-3 bg-gray-100">
                            <div class="flex items-center space-x-3">
                                <button
                                    @click="abrirNuevaPestana"
                                    class="flex items-center space-x-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors"
                                    title="Abrir nueva pestaña"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Abrir nueva pestaña</span>
                                </button>
                                <button
                                    @click="descargarPdf"
                                    class="flex items-center space-x-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors"
                                    title="Guardar en PDF"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Descargar PDF</span>
                                </button>
                                <button
                                    @click="imprimirPdf"
                                    class="flex items-center space-x-2 px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition-colors"
                                    title="Imprimir"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Imprimir</span>
                                </button>
                            </div>

                            <!-- Botón de cerrar -->
                            <button
                                @click="cerrarPdf"
                                class="flex items-center justify-center w-10 h-10 text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                                title="Cerrar"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Contenido del PDF -->
                    <div class="flex-1 h-full">
                        <iframe
                            v-if="pdfUrl"
                            :src="pdfUrl"
                            class="w-full h-full border-0"
                            title="Recibo de Compra"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Compra {
    id_compra: number;
    fecha: string;
    total: number;
    proveedor: {
        nombre: string;
    };
    usuario: {
        name: string;
    };
}

interface Proveedor {
    id_proveedor: number;
    nombre: string;
}

interface CompraPaginated {
    data: Compra[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Filters {
    search: string;
}

const props = defineProps<{
    compras: CompraPaginated;
    filters?: Filters;
}>();

const filters = ref<Filters>({
    search: props.filters?.search || ''
});

// Variables para el modal del PDF
const mostrarPdf = ref(false);
const pdfUrl = ref('');
const pdfData = ref(null);

// Variable para el debounce
let searchTimeout: NodeJS.Timeout | null = null;

// Asegurar que siempre tengamos un objeto válido
if (!props.filters) {
    filters.value = {
        search: ''
    };
}

const applyFilters = () => {
    console.log('Aplicando filtros:', filters.value);
    router.get(route('compras.index'), { search: filters.value.search }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const clearFilters = () => {
    filters.value = {
        search: ''
    };
    applyFilters();
};

// Función de búsqueda con debounce
const debounceSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        console.log('Ejecutando búsqueda con:', filters.value.search);
        router.get(route('compras.index'), { search: filters.value.search }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                console.log('Búsqueda completada exitosamente');
            },
            onError: (errors) => {
                console.error('Error en búsqueda:', errors);
            }
        });
    }, 300); // Reducido a 300ms para mejor respuesta
};

const deleteCompra = (id: number) => {
    if (confirm('¿Estás seguro de que quieres eliminar esta compra?')) {
        router.delete(route('compras.destroy', id));
    }
};

const imprimirRecibo = async (compra: any) => {
    try {
        const response = await fetch(route('compras.recibo', compra.id_compra), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        const result = await response.json();

        if (result.success) {
            // Convertir base64 a blob y crear URL
            const pdfBlob = new Blob([Uint8Array.from(atob(result.pdf), c => c.charCodeAt(0))], { type: 'application/pdf' });
            const url = URL.createObjectURL(pdfBlob);

            // Guardar datos del PDF
            pdfData.value = result;
            pdfUrl.value = url;

            // Mostrar modal
            mostrarPdf.value = true;
        } else {
            alert('Error al generar el recibo: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al generar el recibo');
    }
};

const formatDate = (date: string) => {
    try {
        // Parsear la fecha manualmente para evitar problemas de zona horaria
        const parts = date.split('-');
        if (parts.length === 3) {
            const year = parseInt(parts[0]);
            const month = parseInt(parts[1]) - 1; // Los meses en JS van de 0-11
            const day = parseInt(parts[2]);

            // Crear fecha usando el constructor con parámetros individuales
            const fecha = new Date(year, month, day);

            // Verificar si la fecha es válida
            if (isNaN(fecha.getTime())) {
                return 'Fecha inválida';
            }

            return fecha.toLocaleDateString('es-BO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        } else {
            // Si no es formato YYYY-MM-DD, intentar parsear normalmente
            const fecha = new Date(date);
            if (isNaN(fecha.getTime())) {
                return 'Fecha inválida';
            }

            return fecha.toLocaleDateString('es-BO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
    } catch (error) {
        return 'Fecha inválida';
    }
};



const formatCurrency = (amount: number) => {
    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

// Métodos para manejar el modal del PDF
const cerrarPdf = () => {
    mostrarPdf.value = false;
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = '';
    }
    pdfData.value = null;
};

const descargarPdf = () => {
    if (pdfData.value) {
        const link = document.createElement('a');
        link.href = pdfUrl.value;
        link.download = pdfData.value.filename || 'recibo_compra.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

const abrirNuevaPestana = () => {
    if (pdfUrl.value) {
        window.open(pdfUrl.value, '_blank');
    }
};

const imprimirPdf = () => {
    if (pdfUrl.value) {
        const printWindow = window.open(pdfUrl.value);
        printWindow.onload = () => {
            printWindow.print();
        };
    }
};
</script>

