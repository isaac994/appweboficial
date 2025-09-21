<template>
    <AppLayout :title="`Editar Venta #${venta.id_venta}`">
        <template #header>
            <Heading>Editar Venta #{{ venta.id_venta }}</Heading>
        </template>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Información de la Venta</CardTitle>
                    <CardDescription>
                        Modifique los datos de la venta según sea necesario
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="updateVenta" class="space-y-6">
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
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                                                    v-for="prod in productosDisponibles"
                                                    :key="prod.id_producto"
                                                    :value="prod.id_producto"
                                                >
                                                    {{ prod.nombre }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <Label :for="`cantidad-${index}`">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad-${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                :min="isSmartphone(selectedProductos[index]) ? 1 : 1"
                                                :max="isSmartphone(selectedProductos[index]) ? 1 : undefined"
                                                class="mt-1"
                                                required
                                                @input="handleCantidadChange(index, $event.target.value)"
                                            />
                                            <p v-if="isSmartphone(selectedProductos[index])" class="text-xs text-amber-600 mt-1">
                                                ⚠️ Los celulares solo se venden de uno en uno
                                            </p>
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

                                        <div v-if="isSmartphone(selectedProductos[index])">
                                            <Label :for="`descripcion-${index}`">Descripción/IMEI</Label>
                                            <Input
                                                :id="`descripcion-${index}`"
                                                v-model="producto.descripcion"
                                                type="text"
                                                placeholder="Ingrese el IMEI del celular..."
                                                class="mt-1"
                                            />
                                        </div>

                                        <div class="flex items-end">
                                            <div class="flex-1">
                                                <Label>Total Parcial</Label>
                                                <div class="mt-1 text-lg font-semibold text-green-600">
                                                    {{ formatCurrency(producto.total_parcial || 0) }}
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
                                <span class="text-green-600">{{ formatCurrency(totalVenta) }}</span>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3">
                            <Button
                                type="button"
                                @click="cancelar"
                                variant="outline"
                            >
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="form.productos.length === 0">
                                Actualizar Venta
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
    stock_disponible: number;
    estado_disponible: string;
    categoria?: { nombre: string };
    marca?: { nombre: string };
}

interface ProductoVenta {
    id_producto: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
    descripcion: string;
}

interface DetalleVenta {
    id_detalle_venta: number;
    id_producto: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
    producto: Producto;
}

interface Venta {
    id_venta: number;
    id_cliente: number;
    fecha: string;
    total: number;
    detalles: DetalleVenta[];
}

interface FormData {
    id_cliente: string;
    fecha: string;
    productos: ProductoVenta[];
}

const props = defineProps<{
    venta: Venta;
    clientes: Cliente[];
    productos: Producto[];
    errors?: Record<string, string>;
}>();

const form = ref<FormData>({
    id_cliente: props.venta.id_cliente.toString(),
    fecha: new Date(props.venta.fecha).toISOString().split('T')[0],
    productos: props.venta.detalles.map(detalle => ({
        id_producto: detalle.id_producto,
        cantidad: detalle.cantidad,
        precio_unitario: detalle.precio_unitario,
        total_parcial: detalle.total_parcial,
        descripcion: detalle.descripcion || ''
    }))
});

// Inicializar selectedProductos con los productos existentes
const selectedProductos = ref<(Producto | null)[]>([]);

onMounted(() => {
    // Inicializar selectedProductos con los productos existentes
    selectedProductos.value = form.value.productos.map(producto => {
        return props.productos.find(p => p.id_producto === producto.id_producto) || null;
    });

    // Recalcular totales para todos los productos al cargar
    form.value.productos.forEach((producto, index) => {
        updateTotal(index);
    });
});

const addProducto = () => {
    form.value.productos.push({
        id_producto: 0,
        cantidad: 1,
        precio_unitario: 0,
        total_parcial: 0,
        descripcion: ''
    });

    // Agregar null a selectedProductos para el nuevo producto
    selectedProductos.value.push(null);
};

const removeProducto = (index: number) => {
    form.value.productos.splice(index, 1);
    selectedProductos.value.splice(index, 1);
};

const updateProductoInfo = (index: number) => {
    const producto = form.value.productos[index];
    const productoInfo = props.productos.find(p => p.id_producto === producto.id_producto);

    if (productoInfo) {
        producto.precio_unitario = productoInfo.precio_venta;

        // Actualizar selectedProductos para el índice actual
        selectedProductos.value[index] = productoInfo;

        // Si es un smartphone, establecer cantidad en 1
        if (isSmartphone(productoInfo)) {
            producto.cantidad = 1;
        }

        updateTotal(index);
    }
};

const updateTotal = (index: number) => {
    const producto = form.value.productos[index];
    producto.total_parcial = producto.cantidad * producto.precio_unitario;
};


const productosDisponibles = computed(() => {
    return props.productos.filter(p => p.stock_disponible > 0);
});

const totalVenta = computed(() => {
    return form.value.productos.reduce((total, producto) => {
        return total + (producto.total_parcial || 0);
    }, 0);
});

const cancelar = () => {
    router.visit(route('ventas.index'));
};

const updateVenta = () => {
    if (form.value.productos.length === 0) {
        alert('Debe agregar al menos un producto');
        return;
    }

    router.put(route('ventas.update', props.venta.id_venta), form.value);
};

const formatCurrency = (amount: number) => {
    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

// Función para verificar si un producto es un smartphone
const isSmartphone = (producto: Producto | null) => {
    return producto?.categoria?.nombre?.toLowerCase() === 'smartphones';
};

// Función para manejar el cambio de cantidad con validación para smartphones
const handleCantidadChange = (index: number, cantidad: number) => {
    const producto = selectedProductos.value[index];

    // Si es un smartphone, limitar la cantidad a 1
    if (isSmartphone(producto) && cantidad > 1) {
        form.value.productos[index].cantidad = 1;
        alert('Los celulares solo pueden venderse de uno en uno debido al IMEI único.');
    } else {
        form.value.productos[index].cantidad = cantidad;
    }

    updateTotal(index);
};
</script>
