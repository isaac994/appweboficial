<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Editar Venta #{{ venta.id_venta }}</h1>
                        <p class="text-blue-300 text-sm">Modifique los datos de la venta según sea necesario</p>
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

                <!-- Formulario -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                    <div class="p-6">
                        <form @submit.prevent="updateVenta" class="space-y-6">
                            <!-- Información Básica -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Cliente -->
                                <div>
                                    <label class="block text-sm font-medium text-blue-300 mb-2">Cliente *</label>
                                    <select
                                        v-model="form.id_cliente"
                                        class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    >
                                        <option value="">Seleccione un cliente</option>
                                        <option
                                            v-for="cliente in clientes"
                                            :key="cliente.id_cliente"
                                            :value="cliente.id_cliente"
                                        >
                                            {{ cliente.nombre }} {{ cliente.apellidos || '' }} - CI: {{ cliente.ci || 'Sin CI' }} - Tel: {{ cliente.telefono || 'Sin teléfono' }}
                                        </option>
                                    </select>
                                    <div v-if="errors.id_cliente" class="mt-1 text-sm text-red-400">
                                        {{ errors.id_cliente }}
                                    </div>
                                </div>

                                <!-- Fecha -->
                                <div>
                                    <label class="block text-sm font-medium text-blue-300 mb-2">Fecha *</label>
                                    <input
                                        v-model="form.fecha"
                                        type="date"
                                        class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                    <div v-if="errors.fecha" class="mt-1 text-sm text-red-400">
                                        {{ errors.fecha }}
                                    </div>
                                </div>
                            </div>

                            <!-- Productos -->
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <label class="text-lg font-semibold text-white">Productos *</label>
                                    <button
                                        type="button"
                                        @click="addProducto"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Agregar Producto
                                    </button>
                                </div>

                                <div v-if="form.productos.length === 0" class="text-center py-8 text-gray-400">
                                    <p>No hay productos agregados</p>
                                    <p class="text-sm">Haga clic en "Agregar Producto" para comenzar</p>
                                </div>

                                <div v-else class="space-y-4">
                                    <div
                                        v-for="(producto, index) in form.productos"
                                        :key="index"
                                        class="bg-black/30 border border-blue-500/30 rounded-lg p-4"
                                    >
                                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                            <!-- Producto -->
                                            <div class="relative">
                                                <label class="block text-sm font-medium text-blue-300 mb-2">Producto *</label>
                                                <div class="relative">
                                                    <input
                                                        v-model="productoSearch[index]"
                                                        type="text"
                                                        placeholder="Buscar producto..."
                                                        class="w-full px-4 py-3 bg-black/50 border border-blue-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10"
                                                        :class="{ 'border-red-500': errors[`productos.${index}.id_producto`] }"
                                                        @input="filterProductos(index)"
                                                        @focus="handleProductoFocus(index)"
                                                        @blur="handleProductoBlur(index)"
                                                    />
                                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                        </svg>
                                                    </div>
                                                </div>

                                                <!-- Dropdown de productos -->
                                                <div v-if="showProductosDropdown[index] && filteredProductos[index] && filteredProductos[index].length > 0"
                                                     class="absolute z-10 w-full mt-1 bg-black/90 border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                                                    <div
                                                        v-for="prod in filteredProductos[index]"
                                                        :key="prod.id_producto"
                                                        @mousedown="selectProducto(index, prod)"
                                                        class="px-4 py-3 hover:bg-blue-500/20 cursor-pointer border-b border-blue-500/20 last:border-b-0"
                                                    >
                                                        <div class="font-medium text-white">{{ prod.modelo?.nombre || 'Sin modelo' }}</div>
                                                        <div class="text-sm text-blue-300">{{ prod.descripcion }}</div>
                                                        <div class="text-xs text-gray-400">{{ prod.categoria?.nombre }} - {{ prod.marca?.nombre }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cantidad -->
                                            <div>
                                                <label class="block text-sm font-medium text-blue-300 mb-2">Cantidad *</label>
                                                <input
                                                    v-model.number="producto.cantidad"
                                                    type="number"
                                                    :min="isSmartphone(selectedProductos[index]) ? 1 : 1"
                                                    :max="isSmartphone(selectedProductos[index]) ? 1 : selectedProductos[index]?.stock_disponible"
                                                    class="w-full px-4 py-3 bg-black/50 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    :class="{ 'border-red-500': errors[`productos.${index}.cantidad`] }"
                                                    required
                                                    @input="handleCantidadChange(index, $event.target.value)"
                                                />
                                                <!-- Error de cantidad -->
                                                <div v-if="errors[`productos.${index}.cantidad`]" class="mt-1 text-sm text-red-400">
                                                    {{ errors[`productos.${index}.cantidad`] }}
                                                </div>
                                            </div>

                                            <!-- Precio Unitario -->
                                            <div>
                                                <label class="block text-sm font-medium text-blue-300 mb-2">Precio Unitario *</label>
                                                <input
                                                    v-model.number="producto.precio_unitario"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :readonly="isOperator"
                                                    class="w-full px-4 py-3 bg-black/50 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    :class="{
                                                        'bg-gray-600/50 cursor-not-allowed': isOperator
                                                    }"
                                                    required
                                                    @input="updateTotal(index)"
                                                />
                                                <!-- Mostrar ganancia -->
                                                <div class="mt-1 text-sm">
                                                    <span class="text-blue-300">Su ganancia será: </span>
                                                    <span class="text-green-400 font-semibold">
                                                        {{ calculateGanancia(index) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- IMEI (solo para smartphones) -->
                                            <div v-if="isSmartphone(selectedProductos[index])">
                                                <label class="block text-sm font-medium text-blue-300 mb-2">Nro IMEI *</label>
                                                <input
                                                    v-model="producto.descripcion"
                                                    type="text"
                                                    placeholder="Ingrese el IMEI del celular..."
                                                    class="w-full px-4 py-3 bg-black/50 border border-blue-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                    required
                                                />
                                            </div>

                                            <!-- Total Parcial y Eliminar -->
                                            <div class="flex items-end">
                                                <div class="flex-1">
                                                    <label class="block text-sm font-medium text-blue-300 mb-2">Total Parcial</label>
                                                    <div class="text-lg font-semibold text-green-400 flex items-center h-10">
                                                        {{ formatCurrency(producto.total_parcial || 0) }}
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="removeProducto(index)"
                                                    class="ml-2 p-2 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded-lg transition-colors duration-200"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t border-blue-500/30 pt-6">
                                <div class="flex justify-between items-center text-xl font-bold">
                                    <span class="text-white">Total de la Venta:</span>
                                    <span class="text-green-400">{{ formatCurrency(totalVenta) }}</span>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="cancelar"
                                    class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.productos.length === 0"
                                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-500 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    Actualizar Venta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { useAuth } from '@/composables/useAuth';

interface Cliente {
    id_cliente: number;
    nombre: string;
    apellidos?: string;
    ci?: string;
    telefono?: string;
}

interface Producto {
    id_producto: number;
    nombre: string;
    precio_venta: number;
    stock_disponible: number;
    estado_disponible: string;
    ultimo_precio_compra?: number;
    categoria?: { nombre: string };
    marca?: { nombre: string };
    modelo?: { nombre: string };
    descripcion?: string;
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
    descripcion?: string;
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

// Obtener información del usuario autenticado
const { isOperator } = useAuth();

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

// Variables para filtrado de productos
const productoSearch = ref<string[]>([]);
const showProductosDropdown = ref<boolean[]>([]);
const filteredProductos = ref<Producto[][]>([]);

onMounted(() => {
    // Inicializar selectedProductos con los productos existentes
    selectedProductos.value = form.value.productos.map(producto => {
        return props.productos.find(p => p.id_producto === producto.id_producto) || null;
    });

    // Inicializar arrays para filtrado de productos
    form.value.productos.forEach((producto, index) => {
        const productoInfo = props.productos.find(p => p.id_producto === producto.id_producto);
        productoSearch.value[index] = productoInfo?.modelo?.nombre || productoInfo?.nombre || 'Sin nombre';
        showProductosDropdown.value[index] = false;
        filteredProductos.value[index] = [];
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

    // Inicializar arrays para el nuevo producto
    const index = form.value.productos.length - 1;
    productoSearch.value[index] = '';
    showProductosDropdown.value[index] = false;
    filteredProductos.value[index] = [];
};

const removeProducto = (index: number) => {
    form.value.productos.splice(index, 1);
    selectedProductos.value.splice(index, 1);

    // Limpiar arrays del producto eliminado
    productoSearch.value.splice(index, 1);
    showProductosDropdown.value.splice(index, 1);
    filteredProductos.value.splice(index, 1);
};

// Métodos para filtrado de productos
const filterProductos = (index: number) => {
    if (!productoSearch.value[index] || !productoSearch.value[index].trim()) {
        filteredProductos.value[index] = props.productos;
    } else {
        const search = productoSearch.value[index].toLowerCase();
        filteredProductos.value[index] = props.productos.filter(producto =>
            (producto.modelo?.nombre || '').toLowerCase().includes(search) ||
            (producto.nombre || '').toLowerCase().includes(search) ||
            (producto.descripcion || '').toLowerCase().includes(search) ||
            (producto.categoria?.nombre || '').toLowerCase().includes(search) ||
            (producto.marca?.nombre || '').toLowerCase().includes(search)
        );
    }
};

const selectProducto = (index: number, producto: Producto) => {
    selectedProductos.value[index] = producto;
    form.value.productos[index].id_producto = producto.id_producto;
    form.value.productos[index].precio_unitario = producto.precio_venta;
    productoSearch.value[index] = producto.modelo?.nombre || producto.nombre || 'Sin nombre';
    showProductosDropdown.value[index] = false;

    // Si es un smartphone, establecer cantidad en 1
    if (isSmartphone(producto)) {
        form.value.productos[index].cantidad = 1;
    }

    updateTotal(index);
};

const handleProductoFocus = (index: number) => {
    showProductosDropdown.value[index] = true;
    if (selectedProductos.value[index]) {
        // Si ya hay un producto seleccionado, limpiar la búsqueda para permitir nueva búsqueda
        productoSearch.value[index] = '';
        selectedProductos.value[index] = null;
        form.value.productos[index].id_producto = 0;
        form.value.productos[index].precio_unitario = 0;
    }
    filterProductos(index);
};

const handleProductoBlur = (index: number) => {
    // Delay para permitir que se ejecute el click en selectProducto
    setTimeout(() => {
        showProductosDropdown.value[index] = false;
    }, 150);
};

const updateTotal = (index: number) => {
    const producto = form.value.productos[index];
    producto.total_parcial = producto.cantidad * producto.precio_unitario;
};

const calculateGanancia = (index: number) => {
    const producto = form.value.productos[index];

    if (!producto.id_producto || !producto.precio_unitario) {
        return 'Bs 0.00';
    }

    // Buscar el producto en la lista original para obtener el ultimo_precio_compra
    const productoCompleto = props.productos.find(p => p.id_producto === producto.id_producto);

    if (!productoCompleto?.ultimo_precio_compra) {
        return 'Bs 0.00';
    }

    const cantidad = Number(producto.cantidad) || 0;
    const precioVenta = Number(producto.precio_unitario) || 0;
    const precioCompra = Number(productoCompleto.ultimo_precio_compra) || 0;

    const gananciaPorUnidad = precioVenta - precioCompra;
    const gananciaTotal = gananciaPorUnidad * cantidad;

    return formatCurrency(gananciaTotal);
};

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

    // Validar stock antes de actualizar la venta
    for (let i = 0; i < form.value.productos.length; i++) {
        const producto = form.value.productos[i];
        const productoInfo = selectedProductos.value[i];

        if (productoInfo && producto.cantidad > productoInfo.stock_disponible) {
            alert(`El producto "${productoInfo.nombre || productoInfo.modelo?.nombre}" no tiene suficiente stock. Disponible: ${productoInfo.stock_disponible} unidades.`);
            return;
        }
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
    return producto?.categoria?.nombre?.toLowerCase() === 'celulares' ||
           producto?.categoria?.nombre?.toLowerCase() === 'smartphones';
};

// Función para manejar el cambio de cantidad con validación para smartphones y stock
const handleCantidadChange = (index: number, cantidad: number) => {
    const producto = selectedProductos.value[index];
    const cantidadNum = parseInt(cantidad) || 0;

    // Validar que la cantidad sea un número válido
    if (isNaN(cantidadNum) || cantidadNum < 1) {
        form.value.productos[index].cantidad = 1;
        updateTotal(index);
        return;
    }

    // Si es un smartphone, limitar la cantidad a 1
    if (isSmartphone(producto) && cantidadNum > 1) {
        form.value.productos[index].cantidad = 1;
        alert('Los celulares solo pueden venderse de uno en uno debido al IMEI único.');
        updateTotal(index);
        return;
    }

    // Validar stock disponible
    if (producto && cantidadNum > producto.stock_disponible) {
        form.value.productos[index].cantidad = producto.stock_disponible;
        alert(`Solo hay ${producto.stock_disponible} unidades disponibles de este producto.`);
        updateTotal(index);
        return;
    }

    // Si todo está bien, actualizar la cantidad
    form.value.productos[index].cantidad = cantidadNum;
    updateTotal(index);
};
</script>
