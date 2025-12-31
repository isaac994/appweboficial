<template>
    <AppLayout :title="`Editar Compra #${compra?.id_compra || 'N/A'}`">
        <template #header>
            <Heading>Editar Compra #{{ compra?.id_compra || 'N/A' }}</Heading>
        </template>

        <!-- Error Messages -->
        <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mb-6">
          <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h3 class="text-red-400 font-semibold">Error</h3>
            </div>
            <ul class="mt-2 text-red-300 text-sm">
              <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
            </ul>
          </div>image.png
        </div>

        <!-- Success Messages -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6">
          <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-green-400 font-semibold">{{ $page.props.flash.success }}</p>
            </div>
          </div>
        </div>

        <div v-if="compra && proveedores && productos" class="max-w-4xl mx-auto space-y-6">
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                <div class="p-6 border-b border-blue-500/30">
                    <h2 class="text-2xl font-bold text-white">Información de la Compra</h2>
                    <p class="text-gray-300 text-sm mt-1">Modifique los datos de la compra según sea necesario</p>
                </div>
                <div class="p-6">
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Información básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <Label for="id_proveedor" class="text-blue-300">Proveedor *</Label>
                                <select
                                    id="id_proveedor"
                                    v-model="form.id_proveedor"
                                    required
                                    class="mt-1 block w-full px-4 py-2 bg-black/30 text-white border border-blue-500/50 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': errors.id_proveedor }"
                                >
                                    <option value="" class="bg-black text-white">Seleccione un proveedor</option>
                                    <option
                                        v-for="proveedor in (proveedores || [])"
                                        :key="proveedor.id_proveedor"
                                        :value="proveedor.id_proveedor"
                                        class="bg-black text-white"
                                    >
                                        {{ proveedor.nombre }}
                                    </option>
                                </select>
                                <p v-if="errors.id_proveedor" class="mt-1 text-sm text-red-400">
                                    {{ errors.id_proveedor }}
                                </p>
                            </div>

                            <div>
                                <Label for="fecha" class="text-blue-300">Fecha *</Label>
                                <Input
                                    id="fecha"
                                    v-model="form.fecha"
                                    type="date"
                                    required
                                    class="mt-1 text-white bg-black/30 border-blue-500/50"
                                    :class="{ 'border-red-500': errors.fecha }"
                                />
                                <p v-if="errors.fecha" class="mt-1 text-sm text-red-400">
                                    {{ errors.fecha }}
                                </p>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <Label class="text-lg font-semibold text-white">Productos</Label>
                                <Button type="button" @click="addProduct" variant="outline" size="sm">
                                    Agregar Producto
                                </Button>
                            </div>

                            <div v-if="form.productos.length === 0" class="text-center py-8 text-gray-400">
                                <p>No hay productos agregados</p>
                                <p class="text-sm">Haga clic en "Agregar Producto" para comenzar</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(producto, index) in form.productos"
                                    :key="index"
                                    class="border border-blue-500/30 rounded-lg p-4 bg-black/20 backdrop-blur-sm"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-medium text-white">Producto {{ index + 1 }}</h4>
                                        <Button
                                            type="button"
                                            @click="removeProduct(index)"
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-600 hover:text-red-700"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div class="relative">
                                            <Label :for="`producto_${index}`" class="text-blue-300">Producto *</Label>
                                            <div class="relative">
                                                <Input
                                                    :id="`producto_${index}`"
                                                    v-model="productoSearch[index]"
                                                    type="text"
                                                    placeholder="Buscar producto..."
                                                    class="mt-1 pr-10 text-white bg-black/30 border-blue-500/50"
                                                    :class="{
                                                        'border-blue-500 bg-blue-500/20': selectedProductos[index]
                                                    }"
                                                    @input="filterProductos(index)"
                                                    @focus="handleProductoFocus(index)"
                                                    @blur="handleProductoBlur(index)"
                                                />
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Dropdown de productos -->
                                            <div v-if="showProductosDropdown[index] && filteredProductos[index] && filteredProductos[index].length > 0"
                                                 class="absolute z-10 w-full mt-1 bg-[#0a1628] border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                                                <div
                                                    v-for="prod in filteredProductos[index]"
                                                    :key="prod.id_producto"
                                                    @mousedown="selectProducto(index, prod)"
                                                    class="px-4 py-2 hover:bg-blue-600/20 cursor-pointer border-b border-blue-500/20 last:border-b-0"
                                                >
                                                    <div class="font-medium text-white">{{ prod.modelo?.nombre || 'Sin modelo' }}</div>
                                                    <div class="text-sm text-gray-300">{{ prod.descripcion }}</div>
                                                    <div class="text-xs text-gray-400">{{ prod.categoria?.nombre }} - {{ prod.marca?.nombre }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <Label :for="`cantidad_${index}`" class="text-blue-300">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad_${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                min="1"
                                                required
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                @input="calculateTotal"
                                            />
                                        </div>

                                        <div>
                                            <Label :for="`precio_${index}`" class="text-blue-300">Precio Unitario *</Label>
                                            <Input
                                                :id="`precio_${index}`"
                                                v-model.number="producto.precio_unitario"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                required
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                @input="calculateTotal"
                                            />
                                        </div>

                                        <div>
                                            <Label class="text-blue-300">Total Parcial</Label>
                                            <div class="mt-1 p-2 bg-green-500/20 rounded-md text-green-300 font-mono">
                                                {{ formatCurrency(producto.cantidad * producto.precio_unitario) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-blue-500/30 pt-6">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-white">Total de la Compra:</span>
                                <div class="text-right">
                                    <span class="text-2xl font-bold" :class="totalCompra <= 0 ? 'text-red-400' : 'text-green-400'">
                                        {{ formatCurrency(totalCompra) }}
                                    </span>
                                    <div v-if="totalCompra <= 0" class="text-sm text-red-400 mt-1">
                                        El total debe ser mayor a Bs 0.00
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end space-x-3">
                            <Button type="button" variant="outline" @click="$inertia.visit(route('compras.index'))">
                                Cancelar
                            </Button>
                            <Button type="submit" :disabled="form.productos.length === 0 || totalCompra <= 0">
                                Actualizar Compra
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-else class="max-w-4xl mx-auto space-y-6">
            <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <span class="ml-3 text-gray-300">Cargando datos de la compra...</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Swal from 'sweetalert2';

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
    id_proveedor: props.compra?.id_proveedor || 0,
    fecha: props.compra?.fecha ? props.compra.fecha.split('T')[0] : '',
    productos: props.compra?.detalles ? props.compra.detalles.map(detalle => ({
        id_producto: detalle.producto.id_producto,
        cantidad: detalle.cantidad,
        precio_unitario: detalle.precio_unitario
    })) : []
});

// Estados para filtro de búsqueda de productos
const productoSearch = ref<string[]>([]);
const selectedProductos = ref<(Producto | null)[]>([]);
const showProductosDropdown = ref<boolean[]>([]);
const filteredProductos = ref<Producto[][]>([]);

const addProduct = () => {
    form.productos.push({
        id_producto: 0,
        cantidad: 1,
        precio_unitario: 0
    });

    const index = form.productos.length - 1;
    productoSearch.value[index] = '';
    showProductosDropdown.value[index] = false;
    selectedProductos.value[index] = null;
    filteredProductos.value[index] = props.productos;
};

const removeProduct = (index: number) => {
    form.productos.splice(index, 1);
    productoSearch.value.splice(index, 1);
    showProductosDropdown.value.splice(index, 1);
    selectedProductos.value.splice(index, 1);
    filteredProductos.value.splice(index, 1);
    calculateTotal();
};

const calculateTotal = () => {
    // El total se calcula automáticamente en el computed
};

// Funciones para el filtro de búsqueda de productos
const filterProductos = (index: number) => {
    if (!productoSearch.value[index]?.trim()) {
        filteredProductos.value[index] = props.productos;
    } else {
        const search = productoSearch.value[index].toLowerCase();
        filteredProductos.value[index] = props.productos.filter(producto =>
            producto.modelo?.nombre.toLowerCase().includes(search)
        );
    }
};

const selectProducto = (index: number, producto: Producto) => {
    selectedProductos.value[index] = producto;
    form.productos[index].id_producto = producto.id_producto;
    productoSearch.value[index] = producto.modelo?.nombre || 'Sin modelo';
    showProductosDropdown.value[index] = false;
};

const handleProductoFocus = (index: number) => {
    showProductosDropdown.value[index] = true;
    if (selectedProductos.value[index]) {
        productoSearch.value[index] = '';
        selectedProductos.value[index] = null;
        form.productos[index].id_producto = 0;
        form.productos[index].precio_unitario = 0;
    }
};

const handleProductoBlur = (index: number) => {
    setTimeout(() => {
        showProductosDropdown.value[index] = false;
    }, 300);
};

const totalCompra = computed(() => {
    return form.productos.reduce((total, producto) => {
        return total + (producto.cantidad * producto.precio_unitario);
    }, 0);
});

const submitForm = () => {
    // Validar que el total sea mayor a 0
    if (totalCompra.value <= 0) {
        Swal.fire({
            title: 'Error de Validación',
            text: 'El total de la compra debe ser mayor a Bs 0.00',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Validar que haya al menos un producto
    if (form.productos.length === 0) {
        Swal.fire({
            title: 'Error de Validación',
            text: 'Debe agregar al menos un producto',
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    form.put(route('compras.update', props.compra?.id_compra), {
        onSuccess: () => {
            // Mostrar mensaje flotante de éxito
            Swal.fire({
                title: '¡Compra Actualizada!',
                text: 'La compra se ha actualizado exitosamente',
                icon: 'success',
                confirmButtonText: 'Continuar',
                confirmButtonColor: '#10b981',
                toast: true,
                position: 'top',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-popup-success',
                    title: 'swal-title-success',
                    content: 'swal-content-success'
                }
            });
        },
        onError: (errors) => {
            console.error('Errores al actualizar compra:', errors);
        }
    });
};

const formatCurrency = (amount: number) => {
    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

// Inicialización
onMounted(() => {
    // Inicializar variables del filtro para productos existentes
    if (props.compra?.detalles) {
        props.compra.detalles.forEach((detalle, index) => {
            productoSearch.value[index] = detalle.producto.modelo?.nombre || 'Sin modelo';
            selectedProductos.value[index] = detalle.producto;
            showProductosDropdown.value[index] = false;
            filteredProductos.value[index] = props.productos;
        });
    }
});
</script>

<style scoped>
:deep(.swal-popup-success) {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 2px solid #10b981;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
}

:deep(.swal-title-success) {
    color: #059669;
    font-weight: 700;
    font-size: 1.2rem;
}

:deep(.swal-content-success) {
    color: #374151;
    font-size: 1rem;
    line-height: 1.5;
}
</style>
