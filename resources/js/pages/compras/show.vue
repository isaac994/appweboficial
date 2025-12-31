<template>
    <AppLayout :title="`Compra #${compra.id_compra}`">
        <template #header>
            <div class="flex items-center justify-between">
                <Heading>Compra #{{ compra.id_compra }}</Heading>
                <div class="flex space-x-2">
                    <Button :href="route('compras.edit', compra.id_compra)" variant="outline">
                        Editar
                    </Button>
                    <Button :href="route('compras.index')" variant="ghost">
                        Volver
                    </Button>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-6">
            <!-- Botón Volver -->
            <div class="mb-6">
                <Link
                    :href="route('compras.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver a Compras
                </Link>
            </div>

            <!-- Tarjeta Principal con toda la información -->
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                <div class="p-6 border-b border-blue-500/30">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-2xl font-bold text-white">Compra #{{ compra.id_compra }}</h2>
                            <p class="text-sm text-blue-300 mt-1">
                                {{ formatDate(compra.fecha) }} • {{ compra.usuario?.name }}
                            </p>
                            <div class="mt-2 text-sm text-gray-300">
                                <span class="font-medium">Proveedor:</span> {{ compra.proveedor?.nombre }}
                                <span v-if="compra.proveedor?.telefono" class="ml-4">
                                    <span class="font-medium">Tel:</span> {{ compra.proveedor.telefono }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Productos de la Compra -->
                    <div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-black/30 border-b-2 border-blue-500/20">
                                        <th class="text-left py-3 px-4 font-semibold text-blue-300">Producto</th>
                                        <th class="text-center py-3 px-4 font-semibold text-blue-300">Cantidad</th>
                                        <th class="text-right py-3 px-4 font-semibold text-blue-300">Precio Unit.</th>
                                        <th class="text-right py-3 px-4 font-semibold text-blue-300">Total Parcial</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(detalle, index) in compra.detalles"
                                        :key="detalle.id_detalle_compra"
                                        class="border-b border-blue-500/20 hover:bg-blue-500/10 transition-colors"
                                    >
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-white">
                                                {{ detalle.producto?.modelo?.nombre || 'Sin modelo' }}
                                            </div>
                                            <div class="text-sm text-gray-300">
                                                {{ detalle.producto?.descripcion || 'Sin descripción' }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                ID: {{ detalle.producto?.id_producto }}
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-300">
                                                {{ detalle.cantidad }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right font-mono text-white">
                                            {{ formatCurrency(detalle.precio_unitario) }}
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <span class="font-semibold text-green-400 text-lg">
                                                {{ formatCurrency(calculateTotalParcial(detalle)) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen de Totales -->
                        <div class="mt-6 p-4 bg-green-500/20 backdrop-blur-sm rounded-lg border border-green-500/30">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-white">{{ compra.detalles.length }}</div>
                                    <div class="text-sm text-blue-300">Productos</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-400">{{ totalProductos }}</div>
                                    <div class="text-sm text-blue-300">Unidades</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-400">{{ formatCurrency(calculateTotalCompra) }}</div>
                                    <div class="text-sm text-green-300">Total</div>
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
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';

interface Modelo {
    id_modelo: number;
    nombre: string;
}

interface Producto {
    id_producto: number;
    descripcion?: string;
    modelo?: Modelo;
}

interface Proveedor {
    id_proveedor: number;
    nombre: string;
    telefono?: string;
    correo?: string;
    direccion?: string;
}

interface Usuario {
    id: number;
    name: string;
}

interface DetalleCompra {
    id_detalle_compra: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial?: number;
    producto: Producto;
}

interface Compra {
    id_compra: number;
    fecha: string;
    total: number;
    proveedor: Proveedor;
    usuario: Usuario;
    detalles: DetalleCompra[];
}

const props = defineProps<{
    compra: Compra;
}>();

const totalProductos = computed(() => {
    return props.compra.detalles.reduce((total, detalle) => total + detalle.cantidad, 0);
});

// Computed para calcular el total de la compra dinámicamente
const calculateTotalCompra = computed(() => {
    return props.compra.detalles.reduce((total, detalle) => {
        return total + calculateTotalParcial(detalle);
    }, 0);
});

// Función para calcular el total parcial dinámicamente
const calculateTotalParcial = (detalle: DetalleCompra) => {
    // Si ya existe total_parcial, lo usamos, sino lo calculamos
    if (detalle.total_parcial && detalle.total_parcial > 0) {
        return detalle.total_parcial;
    }
    // Calculamos dinámicamente: cantidad * precio_unitario
    return detalle.cantidad * detalle.precio_unitario;
};

// Función para formatear fecha correctamente (sin problemas de zona horaria)
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
                month: 'long',
                day: 'numeric',
                weekday: 'long'
            });
        } else {
            // Si no es formato YYYY-MM-DD, intentar parsear normalmente
            const fecha = new Date(date);
            if (isNaN(fecha.getTime())) {
                return 'Fecha inválida';
            }

            return fecha.toLocaleDateString('es-BO', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long'
            });
        }
    } catch (error) {
        return 'Fecha inválida';
    }
};

const formatCurrency = (amount: number) => {
    if (isNaN(amount) || amount === null || amount === undefined) {
        return 'Bs 0.00';
    }
    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};
</script>
