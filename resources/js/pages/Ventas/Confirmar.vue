<template>
    <AppLayout>
        <div v-if="!venta" class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <p class="text-white text-lg">Cargando datos de la venta...</p>
            </div>
        </div>
        <div v-else class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-4">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h1 class="text-2xl font-bold text-white mb-1">Confirmar Venta</h1>
                    <p class="text-blue-300 text-sm">Revisa los datos antes de finalizar la transacción</p>
                </div>

                <!-- Información del Cliente -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-4">
                    <div class="p-4 border-b border-blue-500/30">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Información del Cliente
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">Nombre</label>
                                <p class="text-white text-sm">{{ venta.nuevo_cliente?.nombre || venta.cliente?.nombre || 'Cliente no encontrado' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">CI</label>
                                <p class="text-white text-sm">{{ venta.nuevo_cliente?.ci || venta.cliente?.ci || 'Sin CI' }}</p>
                            </div>
                            <div v-if="venta.nuevo_cliente?.telefono || venta.cliente?.telefono">
                                <label class="block text-xs font-medium text-blue-300 mb-1">Teléfono</label>
                                <p class="text-white text-sm">{{ venta.nuevo_cliente?.telefono || venta.cliente?.telefono }}</p>
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
                            Productos de la Venta
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="text-left py-2 px-3 font-medium text-blue-300 text-sm">Producto</th>
                                    <th class="text-left py-2 px-3 font-medium text-blue-300 text-sm">Categoría</th>
                                    <th class="text-center py-2 px-3 font-medium text-blue-300 text-sm">Cantidad</th>
                                    <th class="text-center py-2 px-3 font-medium text-blue-300 text-sm">IMEI</th>
                                    <th class="text-right py-2 px-3 font-medium text-blue-300 text-sm">Precio Unit.</th>
                                    <th class="text-right py-2 px-3 font-medium text-blue-300 text-sm">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(detalle, index) in venta.detalles" :key="index" class="border-b border-blue-500/20 hover:bg-blue-500/10">
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
                                    <td class="py-2 px-3 text-center">
                                        <span v-if="detalle.descripcion" class="text-green-300 text-xs font-mono bg-green-500/20 px-2 py-1 rounded">{{ detalle.descripcion }}</span>
                                        <span v-else class="text-gray-400 text-xs">-</span>
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
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Resumen compacto -->
                            <div class="flex flex-col sm:flex-row gap-4 lg:gap-8">
                                <div class="text-center">
                                    <p class="text-blue-300 text-xs font-medium mb-1">Fecha</p>
                                    <p class="text-white text-sm font-bold">{{ formatDate(venta.fecha) }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-blue-300 text-xs font-medium mb-1">Productos</p>
                                    <p class="text-white text-sm font-bold">{{ venta.detalles.length }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-blue-300 text-xs font-medium mb-1">Total</p>
                                    <p class="text-green-400 text-lg font-bold">Bs. {{ venta.total.toLocaleString() }}</p>
                                </div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="flex gap-3">
                                <button
                                    @click="cancelarVenta"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </button>

                                <button
                                    @click="volverVenta"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Volver
                                </button>

                                <button
                                    @click="finalizarVenta"
                                    :disabled="procesando"
                                    class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg v-if="!procesando" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ procesando ? 'Finalizando...' : 'Finalizar Venta' }}
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
import AppLayout from '@/layouts/AppLayout.vue';
import Swal from 'sweetalert2';

interface Cliente {
    nombre: string;
    correo_electronico: string;
    telefono?: string;
}

interface Producto {
    nombre: string;
    descripcion?: string;
    modelo: {
        nombre: string;
    };
    marca: {
        nombre: string;
    };
    categoria: {
        nombre: string;
    };
}

interface DetalleVenta {
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
    descripcion?: string;
    producto: Producto;
}

interface Venta {
    id_venta: number;
    fecha: string;
    total: number;
    cliente: Cliente;
    nuevo_cliente?: {
        nombre: string;
        apellidos?: string;
        ci?: string;
        telefono?: string;
    };
    detalles: DetalleVenta[];
}

const props = defineProps<{
    venta: Venta;
}>();

const procesando = ref(false);

// Formatear fecha para mostrar
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

const cancelarVenta = () => {
    router.post(route('ventas.cancelar'), {}, {
        onSuccess: () => {
            // El mensaje de cancelación se mostrará automáticamente
        }
    });
};

const volverVenta = () => {
    // Volver a la interfaz anterior con los datos llenados
    // Pasar los datos de la venta actual para que se recuperen
    console.log('Datos que se van a pasar a Create.vue:', props.venta);
    console.log('Nuevo cliente:', props.venta.nuevo_cliente);
    router.visit(route('ventas.create', { venta_edicion: props.venta }));
};

const finalizarVenta = async () => {
    if (procesando.value) return;

    procesando.value = true;

    try {
        // Enviar POST a la ruta de finalización
        router.post(route('ventas.finalizar'), {}, {
            onSuccess: () => {
                // Mostrar mensaje de éxito
                Swal.fire({
                    title: '¡Venta Creada Exitosamente!',
                    text: 'La venta ha sido registrada correctamente en el sistema.',
                    icon: 'success',
                    confirmButtonText: 'Continuar',
                    confirmButtonColor: '#10b981',
                    background: '#0a1628',
                    color: '#ffffff',
                    customClass: {
                        popup: 'border border-blue-500/30',
                        title: 'text-green-400',
                        confirmButton: 'bg-green-600 hover:bg-green-700'
                    }
                });
            },
            onError: (errors) => {
                console.error('Error al finalizar venta:', errors);
                const values = Object.values(errors || {});
                const imeiError = values.find(v => typeof v === 'string' && v.toLowerCase().includes('imei')) as string | undefined;
                if (imeiError) {
                    Swal.fire({
                        title: 'IMEI duplicado',
                        text: imeiError || 'El nro de IMEI ya existe. No puede repetirse.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444',
                        background: '#0a1628',
                        color: '#ffffff',
                        customClass: {
                            popup: 'border border-red-500/30',
                            title: 'text-red-400',
                            confirmButton: 'bg-red-600 hover:bg-red-700'
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error al Finalizar Venta',
                        text: 'Ocurrió un error al procesar la venta. Por favor, inténtalo de nuevo.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444',
                        background: '#0a1628',
                        color: '#ffffff',
                        customClass: {
                            popup: 'border border-red-500/30',
                            title: 'text-red-400',
                            confirmButton: 'bg-red-600 hover:bg-red-700'
                        }
                    });
                }
            }
        });
    } catch (error) {
        console.error('Error al finalizar venta:', error);
        Swal.fire({
            title: 'Error del Sistema',
            text: 'Ocurrió un error inesperado. Por favor, inténtalo de nuevo.',
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#ef4444',
            background: '#0a1628',
            color: '#ffffff',
            customClass: {
                popup: 'border border-red-500/30',
                title: 'text-red-400',
                confirmButton: 'bg-red-600 hover:bg-red-700'
            }
        });
    } finally {
        procesando.value = false;
    }
};
</script>
