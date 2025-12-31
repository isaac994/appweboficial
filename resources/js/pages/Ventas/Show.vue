<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header compacto -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Detalles de la Venta #{{ venta.id_venta }}</h1>
                        <p class="text-blue-300 text-sm">Información completa de la venta</p>
                    </div>
                    <button
                        @click="router.visit(route('ventas.index'))"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </button>
                </div>

                <!-- Información del Cliente -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-4">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-white mb-3">Información del Cliente</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">Nombre</label>
                                <p class="text-white text-sm">{{ venta.cliente?.nombre || 'Sin nombre' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">Apellidos</label>
                                <p class="text-white text-sm">{{ venta.cliente?.apellidos || 'Sin apellidos' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">CI</label>
                                <p class="text-white text-sm">{{ venta.cliente?.ci || 'Sin CI' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-blue-300 mb-1">Teléfono</label>
                                <p class="text-white text-sm">{{ venta.cliente?.telefono || 'Sin teléfono' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos de la Venta -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden mb-4">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-white mb-3">Productos de la Venta</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full bg-[#0a1628] border border-blue-500/30 rounded-lg">
                                <thead>
                                    <tr class="border-b border-blue-500/30">
                                        <th class="text-left py-2 px-3 text-blue-300 font-medium text-sm">Producto</th>
                                        <th class="text-left py-2 px-3 text-blue-300 font-medium text-sm">Categoría</th>
                                        <th class="text-center py-2 px-3 text-blue-300 font-medium text-sm">Cantidad</th>
                                        <th class="text-center py-2 px-3 text-blue-300 font-medium text-sm">IMEI</th>
                                        <th class="text-right py-2 px-3 text-blue-300 font-medium text-sm">Precio</th>
                                        <th class="text-right py-2 px-3 text-blue-300 font-medium text-sm">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="detalle in venta.detalles" :key="detalle.id_detalle_venta" class="border-b border-blue-500/20 last:border-b-0">
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
                                    <p class="text-white text-sm font-bold">{{ venta.detalles?.length || 0 }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-blue-300 text-xs font-medium mb-1">Total</p>
                                    <p class="text-green-400 text-lg font-bold">Bs. {{ venta.total?.toLocaleString() || '0' }}</p>
                                </div>
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

interface Cliente {
    nombre: string;
    apellidos?: string;
    ci?: string;
    telefono?: string;
}

interface Producto {
    nombre?: string;
    modelo?: { nombre: string };
    marca?: { nombre: string };
    categoria?: { nombre: string };
    descripcion?: string;
}

interface DetalleVenta {
    id_detalle_venta: number;
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
    detalles: DetalleVenta[];
}

const props = defineProps<{
    venta: Venta;
}>();

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
</script>
