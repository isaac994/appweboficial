<template>
    <AppLayout :title="`Editar Compra #${compra.id_compra}`">
        <template #header>
            <Heading>Editar Compra #{{ compra.id_compra }}</Heading>
        </template>

        <div class="max-w-4xl mx-auto space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Información de la Compra</CardTitle>
                    <CardDescription>
                        Modifique los datos de la compra según sea necesario
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Información básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label for="id_proveedor">Proveedor *</Label>
                                <select
                                    id="id_proveedor"
                                    v-model="form.id_proveedor"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'border-red-500': errors.id_proveedor }"
                                >
                                    <option value="">Seleccione un proveedor</option>
                                    <option
                                        v-for="proveedor in proveedores"
                                        :key="proveedor.id_proveedor"
                                        :value="proveedor.id_proveedor"
                                    >
                                        {{ proveedor.nombre }}
                                    </option>
                                </select>
                                <p v-if="errors.id_proveedor" class="mt-1 text-sm text-red-600">
                                    {{ errors.id_proveedor }}
                                </p>
                            </div>

                            <div>
                                <Label for="fecha">Fecha *</Label>
                                <Input
                                    id="fecha"
                                    v-model="form.fecha"
                                    type="date"
                                    required
                                    class="mt-1"
                                    :class="{ 'border-red-500': errors.fecha }"
                                />
                                <p v-if="errors.fecha" class="mt-1 text-sm text-red-600">
                                    {{ errors.fecha }}
                                </p>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <Label class="text-lg font-semibold">Productos</Label>
                                <Button type="button" @click="addProduct" variant="outline" size="sm">
                                    Agregar Producto
                                </Button>
                            </div>

                            <div v-if="form.productos.length === 0" class="text-center py-8 text-gray-500">
                                <p>No hay productos agregados</p>
                                <p class="text-sm">Haga clic en "Agregar Producto" para comenzar</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(producto, index) in form.productos"
                                    :key="index"
                                    class="border border-gray-200 rounded-lg p-4 bg-gray-50"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-medium text-gray-900">Producto {{ index + 1 }}</h4>
                                        <Button
                                            type="button"
                                            @click="removeProduct(index)"
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            Eliminar
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <Label :for="`producto_${index}`">Producto *</Label>
                                            <select
                                                :id="`producto_${index}`"
                                                v-model="producto.id_producto"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            >
                                                <option value="">Seleccione un producto</option>
                                                <option
                                                    v-for="prod in productos"
                                                    :key="prod.id_producto"
                                                    :value="prod.id_producto"
                                                >
                                                    {{ prod.nombre }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <Label :for="`cantidad_${index}`">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad_${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                min="1"
                                                required
                                                class="mt-1"
                                                @input="calculateTotal"
                                            />
                                        </div>

                                        <div>
                                            <Label :for="`precio_${index}`">Precio Unitario *</Label>
                                            <Input
                                                :id="`precio_${index}`"
                                                v-model.number="producto.precio_unitario"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                required
                                                class="mt-1"
                                                @input="calculateTotal"
                                            />
                                        </div>

                                        <div>
                                            <Label>Total Parcial</Label>
                                            <div class="mt-1 p-2 bg-gray-100 rounded-md text-gray-700 font-mono">
                                                ${{ formatCurrency(producto.cantidad * producto.precio_unitario) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold">Total de la Compra:</span>
                                <span class="text-2xl font-bold text-green-600">
                                    ${{ formatCurrency(totalCompra) }}
                                </span>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3">
                            <Button type="button" variant="outline" @click="$inertia.visit(route('compras.show', compra.id_compra))">
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="form.productos.length === 0">
                                Actualizar Compra
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Producto {
    id_producto: number;
    nombre: string;
}

interface Proveedor {
    id_proveedor: number;
    nombre: string;
}

interface ProductoCompra {
    id_producto: number;
    cantidad: number;
    precio_unitario: number;
}

interface DetalleCompra {
    id_detalle_compra: number;
    cantidad: number;
    precio_unitario: number;
    producto: Producto;
}

interface Compra {
    id_compra: number;
    fecha: string;
    total: number;
    proveedor: Proveedor;
    detalles: DetalleCompra[];
}

const props = defineProps<{
    compra: Compra;
    proveedores: Proveedor[];
    productos: Producto[];
    errors?: Record<string, string>;
}>();

const form = useForm({
    id_proveedor: props.compra.id_proveedor,
    fecha: props.compra.fecha.split('T')[0],
    productos: props.compra.detalles.map(detalle => ({
        id_producto: detalle.producto.id_producto,
        cantidad: detalle.cantidad,
        precio_unitario: detalle.precio_unitario
    }))
});

const addProduct = () => {
    form.productos.push({
        id_producto: 0,
        cantidad: 1,
        precio_unitario: 0
    });
};

const removeProduct = (index: number) => {
    form.productos.splice(index, 1);
    calculateTotal();
};

const calculateTotal = () => {
    // El total se calcula automáticamente en el computed
};

const totalCompra = computed(() => {
    return form.productos.reduce((total, producto) => {
        return total + (producto.cantidad * producto.precio_unitario);
    }, 0);
});

const submitForm = () => {
    if (form.productos.length === 0) {
        alert('Debe agregar al menos un producto');
        return;
    }

    form.put(route('compras.update', props.compra.id_compra));
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};
</script>
