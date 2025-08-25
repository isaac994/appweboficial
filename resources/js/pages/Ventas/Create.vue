<template>
    <AppLayout title="Nueva Venta">
        <template #header>
            <Heading>Crear Nueva Venta</Heading>
        </template>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Información de la Venta</CardTitle>
                    <CardDescription>
                        Complete los datos para crear una nueva venta
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="createVenta" class="space-y-6">
                        <!-- Cliente -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <Label for="cliente">Cliente *</Label>
                                <select
                                    id="cliente"
                                    v-model="form.id_cliente"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option value="">Seleccione un cliente</option>
                                    <option
                                        v-for="cliente in clientes"
                                        :key="cliente.id_cliente"
                                        :value="cliente.id_cliente"
                                    >
                                        {{ cliente.nombre }} - {{ cliente.correo_electronico }}
                                    </option>
                                </select>
                                <div v-if="errors.id_cliente" class="mt-1 text-sm text-red-600">
                                    {{ errors.id_cliente }}
                                </div>
                            </div>

                            <div>
                                <Label for="fecha">Fecha *</Label>
                                <Input
                                    id="fecha"
                                    v-model="form.fecha"
                                    type="date"
                                    class="mt-1"
                                    required
                                />
                                <div v-if="errors.fecha" class="mt-1 text-sm text-red-600">
                                    {{ errors.fecha }}
                                </div>
                            </div>
                        </div>



                        <!-- Productos -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <Label>Productos *</Label>
                                <Button
                                    type="button"
                                    @click="addProducto"
                                    variant="outline"
                                    size="sm"
                                >
                                    + Agregar Producto
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
                                    class="border border-gray-200 rounded-lg p-4"
                                >
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <Label :for="`producto-${index}`">Producto *</Label>
                                            <select
                                                :id="`producto-${index}`"
                                                v-model="producto.id_producto"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                                @change="updateProductoInfo(index)"
                                            >
                                                <option value="">Seleccione un producto</option>
                                                <option
                                                    v-for="prod in productos"
                                                    :key="prod.id_producto"
                                                    :value="prod.id_producto"
                                                >
                                                    {{ prod.nombre }} - ${{ prod.precio_venta }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <Label :for="`cantidad-${index}`">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad-${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                min="1"
                                                class="mt-1"
                                                required
                                                @input="updateTotal(index)"
                                            />

                                        </div>

                                        <div>
                                            <Label :for="`precio-${index}`">Precio Unitario *</Label>
                                            <Input
                                                :id="`precio-${index}`"
                                                v-model.number="producto.precio_unitario"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="mt-1"
                                                required
                                                @input="updateTotal(index)"
                                            />
                                        </div>

                                        <div class="flex items-end">
                                            <div class="flex-1">
                                                <Label>Total Parcial</Label>
                                                <div class="mt-1 text-lg font-semibold text-green-600">
                                                    ${{ formatCurrency(producto.total_parcial || 0) }}
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                @click="removeProducto(index)"
                                                variant="ghost"
                                                size="sm"
                                                class="text-red-600 hover:text-red-700 ml-2"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t pt-6">
                            <div class="flex justify-between items-center text-xl font-bold">
                                <span>Total de la Venta:</span>
                                <span class="text-green-600">${{ formatCurrency(totalVenta) }}</span>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3">
                            <Button
                                type="button"
                                @click="router.visit(route('ventas.index'))"
                                variant="outline"
                            >
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="form.productos.length === 0">
                                Crear Venta
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
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Cliente {
    id_cliente: number;
    nombre: string;
    correo_electronico: string;
}

interface Producto {
    id_producto: number;
    nombre: string;
    precio_venta: number;
    categoria?: { nombre: string };
    marca?: { nombre: string };
}

interface ProductoVenta {
    id_producto: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
}

interface FormData {
    id_cliente: string;
    fecha: string;
    productos: ProductoVenta[];
}

const props = defineProps<{
    clientes: Cliente[];
    productos: Producto[];
    errors?: Record<string, string>;
}>();

const form = ref<FormData>({
    id_cliente: '',
    fecha: new Date().toISOString().split('T')[0],
    productos: []
});

const addProducto = () => {
    form.value.productos.push({
        id_producto: 0,
        cantidad: 1,
        precio_unitario: 0,
        total_parcial: 0
    });
};

const removeProducto = (index: number) => {
    form.value.productos.splice(index, 1);
};

const updateProductoInfo = (index: number) => {
    const producto = form.value.productos[index];
    const productoInfo = props.productos.find(p => p.id_producto === producto.id_producto);

    if (productoInfo) {
        producto.precio_unitario = productoInfo.precio_venta;
        updateTotal(index);
    }
};

const updateTotal = (index: number) => {
    const producto = form.value.productos[index];
    producto.total_parcial = producto.cantidad * producto.precio_unitario;
};



const totalVenta = computed(() => {
    return form.value.productos.reduce((total, producto) => {
        return total + (producto.total_parcial || 0);
    }, 0);
});

const createVenta = () => {
    if (form.value.productos.length === 0) {
        alert('Debe agregar al menos un producto');
        return;
    }

    router.post(route('ventas.store'), form.value);
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};
</script>
