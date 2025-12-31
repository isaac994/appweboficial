<template>
    <AppLayout>
        <div v-if="!compra" class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <p class="text-white text-lg">Cargando datos de la compra...</p>
            </div>
        </div>
        <div v-else class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-4">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="text-2xl font-bold text-white mb-1">Confirmar Compra</h1>
                    <p class="text-blue-300 text-sm">Revisa los datos antes de finalizar la transacción</p>
                </div>

                <!-- Información del Proveedor -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-4">
                    <div class="p-4 border-b border-blue-500/30">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Información del Proveedor
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">Nombre</label>
                                <p class="text-white text-sm">{{ compra.nuevo_proveedor?.nombre || compra.proveedor?.nombre || 'Proveedor no encontrado' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">CI/NIT</label>
                                <p class="text-white text-sm">{{ compra.nuevo_proveedor?.ci_nit || compra.proveedor?.ci_nit || 'Sin CI/NIT' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">Teléfono</label>
                                <p class="text-white text-sm">{{ compra.nuevo_proveedor?.telefono || compra.proveedor?.telefono || 'Sin teléfono' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-4">
                    <div class="p-4 border-b border-blue-500/30">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Productos de la Compra
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="text-left py-2 px-3 font-medium text-blue-300 text-sm">Producto</th>
                                    <th class="text-left py-2 px-3 font-medium text-blue-300 text-sm">Categoría</th>
                                    <th class="text-center py-2 px-3 font-medium text-blue-300 text-sm">Cantidad</th>
                                    <th class="text-right py-2 px-3 font-medium text-blue-300 text-sm">Precio Unit.</th>
                                    <th class="text-right py-2 px-3 font-medium text-blue-300 text-sm">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(detalle, index) in compra.detalles" :key="index" class="border-b border-blue-500/20 hover:bg-blue-500/10">
                                    <td class="py-2 px-3">
                                        <div>
                                            <p class="text-white font-medium text-sm">{{ detalle.producto?.nombre || detalle.producto?.modelo?.nombre || 'Producto no encontrado' }}</p>
                                            <p class="text-blue-300 text-xs">{{ detalle.producto?.marca?.nombre || 'Sin marca' }}</p>
                                            <p class="text-gray-400 text-xs">{{ detalle.producto?.descripcion || 'Sin descripción' }}</p>
                                        </div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300">
                                            {{ detalle.producto?.categoria?.nombre || 'Sin categoría' }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <span class="text-white font-medium text-sm">{{ detalle.cantidad }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <span class="text-white font-medium text-sm">Bs. {{ detalle.precio_unitario.toLocaleString() }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <span class="text-white font-medium text-sm">Bs. {{ detalle.total_parcial.toLocaleString() }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Resumen y Botones en una sola fila -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-4">
                    <div class="p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <!-- Resumen a la izquierda -->
                            <div class="space-y-2">
                                <p class="text-blue-300 text-sm">
                                    <span class="font-medium">Fecha:</span> {{ formatDate(compra.fecha) }}
                                </p>
                                <p class="text-blue-300 text-sm">
                                    <span class="font-medium">Productos:</span> {{ compra.detalles.length }}
                                </p>
                                <p class="text-green-400 text-lg font-bold">
                                    Total: Bs. {{ compra.total.toLocaleString() }}
                                </p>
                            </div>

                            <!-- Botones a la derecha -->
                            <div class="flex gap-2">
                                <button
                                    @click="cancelarCompra"
                                    class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </button>
                                <button
                                    @click="volverAEditar"
                                    class="flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Volver
                                </button>
                                <button
                                    @click="finalizarCompra"
                                    class="flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 font-semibold"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Finalizar Compra
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

interface Producto {
    id_producto: number;
    nombre?: string;
    descripcion?: string;
    modelo?: { nombre: string };
    marca?: { nombre: string };
    categoria?: { nombre: string };
}

interface Detalle {
    id_producto: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
    descripcion?: string;
    producto: Producto;
}

interface Proveedor {
    id_proveedor: number;
    nombre: string;
    ci_nit: string;
    telefono: string;
}

interface Compra {
    id_compra: string;
    fecha: string;
    total: number;
    proveedor?: Proveedor;
    nuevo_proveedor?: {
        nombre: string;
        ci_nit: string;
        telefono: string;
    };
    detalles: Detalle[];
}

const props = defineProps<{
    compra: Compra;
}>();

const compra = ref<Compra | null>(props.compra);

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const cancelarCompra = () => {
    sessionStorage.clear();
    router.visit('/compras');
};

const volverAEditar = () => {
    router.get('/compras/create');
};

const finalizarCompra = () => {
    router.post('/compras/finalizar', {}, {
        onSuccess: () => {
            // Limpiar sessionStorage
            sessionStorage.clear();

            // Mostrar mensaje de éxito
            Swal.fire({
                title: '¡Compra Creada!',
                text: 'La compra se ha registrado exitosamente',
                icon: 'success',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#10b981',
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                allowOutsideClick: false
            });
        }
    });
};
</script>

