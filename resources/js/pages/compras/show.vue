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

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Información de la Compra -->
            <Card>
                <CardHeader>
                    <CardTitle>Información General</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">ID de Compra</Label>
                            <p class="mt-1 text-lg font-mono text-gray-900">#{{ compra.id_compra }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Fecha</Label>
                            <p class="mt-1 text-lg text-gray-900">{{ formatDate(compra.fecha) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Proveedor</Label>
                            <p class="mt-1 text-lg font-semibold text-blue-600">
                                {{ compra.proveedor?.nombre }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Usuario</Label>
                            <p class="mt-1 text-lg text-gray-900">{{ compra.usuario?.name }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Detalles de Productos -->
            <Card>
                <CardHeader>
                    <CardTitle>Productos de la Compra</CardTitle>
                    <CardDescription>
                        Lista de productos adquiridos en esta compra
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Producto</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Cantidad</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Precio Unitario</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Total Parcial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="detalle in compra.detalles"
                                    :key="detalle.id_detalle_compra"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900">
                                            {{ detalle.producto?.nombre }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ detalle.producto?.id_producto }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        <span class="font-medium">{{ detalle.cantidad }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        ${{ formatCurrency(detalle.precio_unitario) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-green-600">
                                            ${{ formatCurrency(detalle.total_parcial) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen -->
                    <div class="mt-6 border-t pt-6">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <p>Total de productos: <span class="font-medium">{{ totalProductos }}</span></p>
                                <p>Total de la compra: <span class="font-semibold text-lg text-green-600">${{ formatCurrency(compra.total) }}</span></p>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Información del Proveedor -->
            <Card v-if="compra.proveedor">
                <CardHeader>
                    <CardTitle>Información del Proveedor</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <Label class="text-sm font-medium text-gray-500">Nombre</Label>
                            <p class="mt-1 text-lg font-semibold text-gray-900">
                                {{ compra.proveedor.nombre }}
                            </p>
                        </div>
                        <div v-if="compra.proveedor.telefono">
                            <Label class="text-sm font-medium text-gray-500">Teléfono</Label>
                            <p class="mt-1 text-lg text-gray-900">{{ compra.proveedor.telefono }}</p>
                        </div>
                        <div v-if="compra.proveedor.correo" class="md:col-span-2">
                            <Label class="text-sm font-medium text-gray-500">Correo Electrónico</Label>
                            <p class="mt-1 text-lg text-gray-900">{{ compra.proveedor.correo }}</p>
                        </div>
                        <div v-if="compra.proveedor.direccion" class="md:col-span-2">
                            <Label class="text-sm font-medium text-gray-500">Dirección</Label>
                            <p class="mt-1 text-lg text-gray-900">{{ compra.proveedor.direccion }}</p>
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
import { Label } from '@/components/ui/label';

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
    total_parcial: number;
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};
</script>
