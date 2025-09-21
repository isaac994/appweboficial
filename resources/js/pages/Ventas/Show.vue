<template>
    <AppLayout :title="`Venta #${venta.id_venta}`">
        <template #header>
            <div class="flex items-center justify-between">
                <Heading>Detalles de la Venta #{{ venta.id_venta }}</Heading>
                <div class="flex space-x-2">
                    <Button
                        @click="router.visit(route('ventas.edit', venta.id_venta))"
                        variant="outline"
                    >
                        Editar
                    </Button>
                    <Button
                        @click="router.visit(route('ventas.index'))"
                        variant="outline"
                    >
                        Volver
                    </Button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Información General -->
            <Card>
                <CardHeader>
                    <CardTitle>Información de la Venta</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Cliente</Label>
                            <div class="mt-1 text-lg font-semibold">{{ venta.cliente?.nombre }}</div>
                            <div class="text-sm text-gray-600">{{ venta.cliente?.correo_electronico }}</div>
                            <div class="text-sm text-gray-600">{{ venta.cliente?.telefono }}</div>
                        </div>

                        <div>
                            <Label class="text-sm font-medium text-gray-500">Fecha</Label>
                            <div class="mt-1 text-lg font-semibold">{{ formatDate(venta.fecha) }}</div>
                        </div>

                        <div>
                            <Label class="text-sm font-medium text-gray-500">Total de Productos</Label>
                            <div class="mt-1 text-lg font-semibold">{{ totalProductos }} productos</div>
                            <div class="text-sm text-gray-600">{{ venta.detalles?.length || 0 }} tipos diferentes</div>
                        </div>
                    </div>


                </CardContent>
            </Card>

            <!-- Productos -->
            <Card>
                <CardHeader>
                    <CardTitle>Productos de la Venta</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Producto</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Categoría</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Marca</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Cantidad</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Precio Unitario</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Total Parcial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="detalle in venta.detalles"
                                    :key="detalle.id_detalle_venta"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900">{{ detalle.producto?.nombre }}</div>
                                        <div class="text-sm text-gray-500">{{ detalle.producto?.descripcion }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ detalle.producto?.categoria?.nombre }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ detalle.producto?.marca?.nombre }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-900 font-mono">
                                        {{ detalle.cantidad }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-900">
                                        {{ formatCurrency(detalle.precio_unitario) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-green-600">
                                            {{ formatCurrency(detalle.total_parcial || 0) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Total -->
                    <div class="border-t pt-6 mt-6">
                        <div class="flex justify-between items-center text-xl font-bold">
                            <span>Total de la Venta:</span>
                            <span class="text-green-600">{{ formatCurrency(venta.total || 0) }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Información del Usuario -->
            <Card>
                <CardHeader>
                    <CardTitle>Información del Vendedor</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ venta.usuario?.name }}</div>
                            <div class="text-sm text-gray-500">{{ venta.usuario?.email }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';

interface Cliente {
    nombre: string;
    correo_electronico: string;
    telefono?: string;
}

interface Producto {
    nombre: string;
    descripcion?: string;
    categoria?: { nombre: string };
    marca?: { nombre: string };
}

interface DetalleVenta {
    id_detalle_venta: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
    producto: Producto;
}

interface Usuario {
    name: string;
    email: string;
}

interface Venta {
    id_venta: number;
    fecha: string;
    total: number;
    cliente: Cliente;
    usuario: Usuario;
    detalles: DetalleVenta[];
}

const props = defineProps<{
    venta: Venta;
}>();

// Computed property para calcular el total de productos vendidos
const totalProductos = computed(() => {
    if (!props.venta.detalles) return 0;
    return props.venta.detalles.reduce((total, detalle) => total + (detalle.cantidad || 0), 0);
});

const formatDate = (date: string) => {
    if (!date) return '';

    // Siempre extraer solo la parte de la fecha (YYYY-MM-DD) para evitar problemas de zona horaria
    const dateOnly = date.split('T')[0]; // Quitar la parte de tiempo si existe
    const [year, month, day] = dateOnly.split('-');

    // Crear fecha en zona horaria local
    const localDate = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));

    return localDate.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatCurrency = (amount: number) => {
    // Manejar valores NaN, null, undefined o no numéricos
    if (!amount || isNaN(amount) || amount === null || amount === undefined) {
        return 'Bs 0.00';
    }

    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};
</script>
