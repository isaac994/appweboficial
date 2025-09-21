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

        <div class="max-w-6xl mx-auto">
            <!-- Tarjeta Principal con toda la información -->
            <Card class="shadow-lg">
                <CardHeader class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                    <div class="flex justify-between items-start">
                        <div>
                            <CardTitle class="text-lg text-gray-800">Compra #{{ compra.id_compra }}</CardTitle>
                            <CardDescription class="text-sm text-gray-600 mt-1">
                                {{ formatDate(compra.fecha) }} • {{ compra.usuario?.name }}
                            </CardDescription>
                            <div class="mt-2 text-sm text-gray-700">
                                <span class="font-medium">Proveedor:</span> {{ compra.proveedor?.nombre }}
                                <span v-if="compra.proveedor?.telefono" class="ml-4">
                                    <span class="font-medium">Tel:</span> {{ compra.proveedor.telefono }}
                                </span>
                            </div>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="p-6">
                    <!-- Productos de la Compra -->
                    <div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-gray-200">
                                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Producto</th>
                                        <th class="text-center py-3 px-4 font-semibold text-gray-700">Cantidad</th>
                                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Precio Unit.</th>
                                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Total Parcial</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(detalle, index) in compra.detalles"
                                        :key="detalle.id_detalle_compra"
                                        class="border-b border-gray-100 hover:bg-gray-50 transition-colors"
                                        :class="{ 'bg-blue-50': index % 2 === 0 }"
                                    >
                                        <td class="py-4 px-4">
                                            <div class="font-medium text-gray-900">
                                                {{ detalle.producto?.nombre || 'Producto no disponible' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ detalle.producto?.id_producto }}
                                            </div>

                                        </td>
                                        <td class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ detalle.cantidad }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-4 text-right font-mono">
                                            {{ formatCurrency(detalle.precio_unitario) }}
                                        </td>
                                        <td class="py-4 px-4 text-right">
                                            <span class="font-semibold text-green-600 text-lg">
                                                {{ formatCurrency(calculateTotalParcial(detalle)) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Resumen de Totales -->
                        <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-800">{{ compra.detalles.length }}</div>
                                    <div class="text-sm text-gray-600">Productos</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-blue-600">{{ totalProductos }}</div>
                                    <div class="text-sm text-gray-600">Unidades</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600">{{ formatCurrency(calculateTotalCompra) }}</div>
                                    <div class="text-sm text-gray-600">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Producto {
    id_producto: number;
    nombre: string;
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
