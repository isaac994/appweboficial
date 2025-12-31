<template>
    <AppLayout title="Nueva Venta">
        <template #header>
            <Heading>Crear Nueva Venta</Heading>
        </template>

        <!-- Mensajes de error globales removidos por solicitud del usuario -->

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

        <div class="space-y-6">
            <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-blue-500/30 p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white mb-2">Información de la Venta</h2>
                    <p class="text-gray-300">Complete los datos para crear una nueva venta</p>
                </div>
                <div>
                    <form @submit.prevent="createVenta" class="space-y-6">
                        <!-- Información del Cliente -->
                        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Información del Cliente</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Campo Unificado de Cliente -->
                                <div class="relative" ref="clientesDropdownRef">
                                    <Label for="cliente" class="text-blue-300">Nombre del Cliente *</Label>
                                    <div class="relative">
                                        <Input
                                            id="cliente"
                                            ref="clienteSearchInput"
                                            v-model="clienteSearch"
                                            type="text"
                                            placeholder="Buscar cliente existente o escribir nombre nuevo..."
                                            class="mt-1 pr-10 text-white bg-black/30 border-blue-500/50"
                                            :class="{ 'border-red-500': errors.id_cliente || errors.nuevo_cliente_nombre }"
                                            @input="filterClientes"
                                            @focus="showClientesDropdown = true"
                                            @blur="handleClienteBlur"
                                        />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <div v-if="showClientesDropdown && filteredClientes.length > 0"
                                         class="absolute z-10 w-full mt-1 bg-[#0a1628] border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                                        <div
                                            v-for="cliente in filteredClientes"
                                            :key="cliente.id_cliente"
                                            @mousedown.prevent="selectCliente(cliente)"
                                            class="px-4 py-2 hover:bg-blue-600/20 cursor-pointer border-b border-blue-500/20 last:border-b-0"
                                        >
                                            <div class="font-medium text-white">{{ cliente.nombre }} {{ cliente.apellidos || '' }}</div>
                                            <div class="text-sm text-gray-400">
                                                <span v-if="cliente.ci">CI: {{ cliente.ci }}</span>
                                                <span v-if="cliente.ci && cliente.telefono"> • </span>
                                                <span v-if="cliente.telefono">Tel: {{ cliente.telefono }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="errors.id_cliente" class="mt-1 text-sm text-red-600">
                                        {{ errors.id_cliente }}
                                    </div>
                                </div>

                                <!-- Fecha -->
                                <div>
                                    <Label for="fecha" class="text-blue-300">Fecha de la Venta *</Label>
                                    <Input
                                        id="fecha"
                                        v-model="form.fecha"
                                        type="date"
                                        class="mt-1 text-white bg-black/30 border-blue-500/50"
                                        :class="{ 'border-red-500': errors.fecha }"
                                        @change="validateDate"
                                    />
                                    <div v-if="errors.fecha" class="mt-1 text-sm text-red-600">
                                        {{ errors.fecha }}
                                    </div>
                                </div>
                            </div>

                            <!-- Campos del Cliente -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <Label class="text-blue-300">Apellidos</Label>
                                    <Input
                                        v-model="nuevoCliente.apellidos"
                                        type="text"
                                        placeholder="Apellidos del cliente"
                                        class="mt-1 text-white bg-black/30 border-blue-500/50"
                                        :class="{ 'border-red-500': errors.nuevo_cliente_apellidos }"
                                    />
                                    <div v-if="errors.nuevo_cliente_apellidos" class="mt-1 text-sm text-red-600">
                                        {{ errors.nuevo_cliente_apellidos }}
                                    </div>
                                </div>
                                <div>
                                    <Label class="text-blue-300">CI</Label>
                                    <Input
                                        v-model="nuevoCliente.ci"
                                        type="text"
                                        placeholder="Cédula de identidad"
                                        class="mt-1 text-white bg-black/30 border-blue-500/50"
                                        :class="{ 'border-red-500': errors.nuevo_cliente_ci }"
                                    />
                                    <div v-if="errors.nuevo_cliente_ci" class="mt-1 text-sm text-red-600">
                                        {{ errors.nuevo_cliente_ci }}
                                    </div>
                                </div>
                                <div>
                                    <Label class="text-blue-300">Teléfono *</Label>
                                    <Input
                                        v-model="nuevoCliente.telefono"
                                        type="text"
                                        placeholder="Número de teléfono"
                                        class="mt-1 text-white bg-black/30 border-blue-500/50"
                                        :class="{ 'border-red-500': errors.nuevo_cliente_telefono }"
                                    />
                                    <div v-if="errors.nuevo_cliente_telefono" class="mt-1 text-sm text-red-600">
                                        {{ errors.nuevo_cliente_telefono }}
                                    </div>
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
                                    class="border border-blue-500/30 bg-black/20 rounded-lg p-4"
                                >
                                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                        <div class="relative">
                                            <Label :for="`producto-${index}`" class="text-gray-300">Producto *</Label>
                                            <div class="relative">
                                                <Input
                                                    :id="`producto-${index}`"
                                                    v-model="productoSearch[index]"
                                                    type="text"
                                                    placeholder="Buscar producto..."
                                                    class="mt-1 pr-10 text-white bg-black/30 border-blue-500/50"
                                                    @input="filterProductos(index)"
                                                    @focus="handleProductoFocus(index)"

                                                />
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1">
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>

                                                                                        <!-- Dropdown de productos -->
                                            <div v-if="showProductosDropdown[index] && filteredProductos[index].length > 0"
                                                 class="absolute z-10 w-full mt-1 bg-[#0a1628] border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                                                <div
                                                    v-for="prod in filteredProductos[index]"
                                                    :key="prod.id_producto"
                                                    @click="selectProducto(index, prod)"
                                                    class="px-4 py-2 hover:bg-blue-600/20 cursor-pointer border-b border-blue-500/20 last:border-b-0"
                                                >
                                                    <div class="font-medium text-white">{{ prod.nombre || prod.modelo?.nombre || 'Sin nombre' }}</div>
                                                    <div class="text-sm text-gray-300">{{ prod.modelo?.nombre || 'Sin modelo' }}</div>
                                                    <div class="text-xs text-gray-400">{{ prod.descripcion || 'Sin descripción' }}</div>
                                                    <div class="text-xs text-blue-300">{{ prod.marca?.nombre || 'Sin marca' }} - {{ prod.categoria?.nombre || 'Sin categoría' }}</div>
                                                    <div class="text-xs text-blue-400">Bs {{ prod.precio_venta || '0.00' }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <Label :for="`cantidad-${index}`" class="text-gray-300">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad-${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                :min="isSmartphone(selectedProductos[index]) ? 1 : 1"
                                                :max="isSmartphone(selectedProductos[index]) ? 1 : selectedProductos[index]?.stock_disponible"
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                :class="{ 'border-red-500': errors[`productos.${index}.cantidad`] }"
                                                required
                                                @input="handleCantidadChange(index, $event.target.value)"
                                            />
                                            <!-- Error de cantidad -->
                                            <div v-if="errors[`productos.${index}.cantidad`]" class="mt-1 text-sm text-red-600">
                                                {{ errors[`productos.${index}.cantidad`] }}
                                            </div>
                                        </div>

                                        <div>
                                            <Label :for="`precio-${index}`" class="text-gray-300">Precio Unitario *</Label>
                                            <Input
                                                :id="`precio-${index}`"
                                                v-model.number="producto.precio_unitario"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :readonly="isOperator"
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                :class="{
                                                    'border-red-500': errors[`productos.${index}.precio_unitario`],
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

                                        <div v-if="isSmartphone(selectedProductos[index])">
                                            <Label :for="`descripcion-${index}`" class="text-gray-300">Nro IMEI *</Label>
                                            <Input
                                                :id="`descripcion-${index}`"
                                                v-model="producto.descripcion"
                                                type="text"
                                                placeholder="Ingrese el IMEI del celular..."
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                :class="{ 'border-red-500': imeiErrors[index] }"
                                                required
                                                @input="validateImeiInline(index)"
                                            />
                                            <div v-if="imeiErrors[index]" class="mt-1 text-sm text-red-600">
                                                {{ imeiErrors[index] }}
                                            </div>
                                        </div>

                                        <div class="flex items-end">
                                            <div class="flex-1">
                                                <Label class="text-gray-300">Total Parcial</Label>
                                                <div class="mt-1 text-lg font-semibold text-white flex items-center h-10">
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
                        <div class="border-t border-blue-500/30 pt-6">
                            <div class="flex justify-between items-center text-xl font-bold">
                                <span class="text-white">Total de la Venta:</span>
                                <span class="text-green-400">{{ formatCurrency(totalVenta) }}</span>
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
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Swal from 'sweetalert2';
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
}

interface ProductoVenta {
    id_producto: number;
    cantidad: number;
    precio_unitario: number;
    total_parcial: number;
    descripcion: string;
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
    venta_edicion?: any;
}>();

// Obtener información del usuario autenticado
const { isOperator } = useAuth();

// Función para obtener la fecha de hoy en formato local
const getTodayDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const form = ref<FormData>({
    id_cliente: '',
    fecha: getTodayDate(),
    productos: []
});

// Errores de IMEI en tiempo real por producto
const imeiErrors = ref<string[]>([]);

// Errores de validación del lado del cliente
const clientErrors = ref<Record<string, string>>({});

// Fecha de hoy para restringir fechas futuras
const todayDate = computed(() => getTodayDate());

// Variables para filtrado de clientes
const clienteSearch = ref('');
const showClientesDropdown = ref(false);
const selectedCliente = ref<Cliente | null>(null);
const filteredClientes = ref<Cliente[]>([]);

// Referencias para el click fuera del dropdown
const clienteSearchInput = ref<HTMLInputElement | null>(null);
const clientesDropdownRef = ref<HTMLElement | null>(null);

// Variables para filtrado de productos
const productoSearch = ref<string[]>([]);
const showProductosDropdown = ref<boolean[]>([]);
const selectedProductos = ref<(Producto | null)[]>([]);
const filteredProductos = ref<Producto[][]>([]);

// Variables para nuevo cliente
const showNuevoCliente = ref(false);
const isClienteExistente = ref(false); // Bandera para distinguir cliente existente vs nuevo
const nuevoCliente = ref({
    nombre: '',
    apellidos: '',
    ci: '',
    telefono: ''
});

const addProducto = () => {
    form.value.productos.push({
        id_producto: 0,
        cantidad: 1,
        precio_unitario: 0,
        total_parcial: 0,
        descripcion: ''
    });

    // Inicializar arrays para el nuevo producto
    const index = form.value.productos.length - 1;
    productoSearch.value[index] = '';
    showProductosDropdown.value[index] = false;
    selectedProductos.value[index] = null;
    filteredProductos.value[index] = [];
    imeiErrors.value[index] = '';
};

const removeProducto = (index: number) => {
    form.value.productos.splice(index, 1);

    // Limpiar arrays del producto eliminado
    productoSearch.value.splice(index, 1);
    showProductosDropdown.value.splice(index, 1);
    selectedProductos.value.splice(index, 1);
    filteredProductos.value.splice(index, 1);
    imeiErrors.value.splice(index, 1);
};

// Métodos para filtrado de clientes
const filterClientes = () => {
    if (!clienteSearch.value.trim()) {
        filteredClientes.value = props.clientes;
    } else {
        const search = clienteSearch.value.toLowerCase();
        filteredClientes.value = props.clientes.filter(cliente =>
            cliente.nombre.toLowerCase().includes(search) ||
            (cliente.apellidos && cliente.apellidos.toLowerCase().includes(search)) ||
            (cliente.ci && cliente.ci.toLowerCase().includes(search)) ||
            (cliente.telefono && cliente.telefono.toLowerCase().includes(search))
        );
    }
};

const selectCliente = (cliente: Cliente) => {
    console.log('Seleccionando cliente:', cliente);

    selectedCliente.value = cliente;
    form.value.id_cliente = cliente.id_cliente.toString();
    clienteSearch.value = `${cliente.nombre} ${cliente.apellidos || ''}`.trim();

    // Marcar como cliente existente
    isClienteExistente.value = true;

    // Llenar campos con datos del cliente existente para mostrar
    Object.assign(nuevoCliente.value, {
        nombre: cliente.nombre,
        apellidos: cliente.apellidos || '',
        ci: cliente.ci || '',
        telefono: cliente.telefono || ''
    });

    console.log('Cliente existente seleccionado, campos llenados:', nuevoCliente.value);
    showClientesDropdown.value = false;
};

// Función para limpiar datos cuando se escribe un nombre nuevo
const clearClienteData = () => {
    if (!selectedCliente.value) {
        // Si no hay cliente seleccionado, limpiar solo si el campo de búsqueda está vacío
        if (clienteSearch.value.trim() === '') {
            Object.assign(nuevoCliente.value, {
                nombre: '',
                apellidos: '',
                ci: '',
                telefono: ''
            });
            form.value.id_cliente = '';
            // Marcar como cliente nuevo cuando se limpia
            isClienteExistente.value = false;
        }
    }
};

const handleClienteInput = () => {
    // Si no se seleccionó un cliente existente, usar el texto como nombre nuevo
    if (!selectedCliente.value && clienteSearch.value.trim()) {
        // SIEMPRE actualizar el nombre con lo que escribió el usuario
        nuevoCliente.value.nombre = clienteSearch.value.trim();
        form.value.id_cliente = ''; // Limpiar ID para cliente nuevo
        // Marcar como cliente nuevo cuando se escribe
        isClienteExistente.value = false;
        console.log('Debug - Actualizando nombre del cliente:', nuevoCliente.value.nombre);
    }
    showClientesDropdown.value = false;
};

const handleClienteBlur = () => {
    // Usar setTimeout con window para evitar problemas de contexto
    window.setTimeout(() => {
        handleClienteInput();
    }, 200);
};

// Función para manejar clicks fuera del dropdown
const handleClickOutside = (event: Event) => {
    if (clientesDropdownRef.value && !clientesDropdownRef.value.contains(event.target as Node)) {
        showClientesDropdown.value = false;
    }
};



// Métodos para filtrado de productos
const filterProductos = (index: number) => {
    if (!productoSearch.value[index]?.trim()) {
        filteredProductos.value[index] = props.productos;
    } else {
        const search = productoSearch.value[index].toLowerCase();
        filteredProductos.value[index] = props.productos.filter(producto =>
            producto.modelo?.nombre.toLowerCase().includes(search) ||
            producto.categoria?.nombre.toLowerCase().includes(search) ||
            producto.marca?.nombre.toLowerCase().includes(search)
        );
    }
};

const selectProducto = (index: number, producto: Producto) => {
    selectedProductos.value[index] = producto;
    form.value.productos[index].id_producto = producto.id_producto;
    form.value.productos[index].precio_unitario = Number(producto.precio_venta) || 0;
    productoSearch.value[index] = producto.nombre || producto.modelo?.nombre || 'Producto seleccionado';
    showProductosDropdown.value[index] = false;

    // Si es un smartphone, establecer cantidad en 1
    if (isSmartphone(producto)) {
        form.value.productos[index].cantidad = 1;
    }

    updateTotal(index);
    // Validar IMEI inline por si ya estaba escrito
    validateImeiInline(index);
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
};



const crearNuevoCliente = () => {
    // Validar que el nombre esté en el campo de búsqueda
    if (!clienteSearch.value.trim()) {
        alert('Por favor escriba el nombre del cliente en el campo de búsqueda');
        return;
    }

    // Validar que los campos obligatorios estén llenos
    if (!nuevoCliente.value.telefono.trim()) {
        alert('Por favor complete el teléfono del cliente');
        return;
    }

    // Crear el cliente temporalmente en el formulario
    const clienteTemp = {
        id_cliente: 'temp_' + Date.now(), // ID temporal
        nombre: clienteSearch.value.trim(),
        apellidos: nuevoCliente.value.apellidos.trim(),
        ci: nuevoCliente.value.ci.trim(),
        telefono: nuevoCliente.value.telefono.trim()
    };

    // Seleccionar el cliente temporal
    selectedCliente.value = clienteTemp;
    form.value.id_cliente = clienteTemp.id_cliente;

    // Agregar el cliente temporal a la lista para que aparezca en el dropdown
    props.clientes.push(clienteTemp);
    filteredClientes.value = props.clientes;

    // Ocultar el formulario de nuevo cliente
    showNuevoCliente.value = false;

    // Limpiar el formulario
    nuevoCliente.value = {
        apellidos: '',
        ci: '',
        telefono: ''
    };
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
    const cantidad = Number(producto.cantidad) || 0;
    const precio = Number(producto.precio_unitario) || 0;
    producto.total_parcial = cantidad * precio;
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

const getProductoInfo = (id_producto: number) => {
    return props.productos.find(p => p.id_producto === id_producto);
};





const totalVenta = computed(() => {
    return form.value.productos.reduce((total, producto) => {
        const totalParcial = Number(producto.total_parcial) || 0;
        return total + totalParcial;
    }, 0);
});

const createVenta = () => {
    console.log('=== INICIANDO createVenta ===');
    console.log('Productos:', form.value.productos.length);

    if (form.value.productos.length === 0) {
        Swal.fire({
            title: 'Error',
            text: 'Debe agregar al menos un producto',
            icon: 'warning',
            toast: true,
            position: 'center',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        return;
    }

    // Validar stock y IMEI antes de crear la venta
    const imeiSet = new Set<string>();
    for (let i = 0; i < form.value.productos.length; i++) {
        const producto = form.value.productos[i];
        const productoInfo = selectedProductos.value[i];

        // Validar stock
        if (productoInfo && producto.cantidad > productoInfo.stock_disponible) {
            Swal.fire({
                title: 'Stock insuficiente',
                text: `El producto "${productoInfo.nombre}" no tiene suficiente stock. Disponible: ${productoInfo.stock_disponible} unidades.`,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Validar IMEI para smartphones
        if (isSmartphone(productoInfo) && (!producto.descripcion || producto.descripcion.trim() === '')) {
            Swal.fire({
                title: 'IMEI requerido',
                text: `Debe ingresar el IMEI para el celular "${productoInfo.modelo?.nombre || 'Sin modelo'}".`,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Validar duplicidad de IMEI dentro del formulario (solo smartphones)
        if (isSmartphone(productoInfo)) {
            const imei = (producto.descripcion || '').trim();
            if (imei) {
                if (imeiSet.has(imei)) {
                    Swal.fire({
                        title: 'IMEI duplicado',
                        text: `El IMEI ${imei} ya fue agregado en esta venta. Cada IMEI debe ser único.`,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                    return;
                }
                imeiSet.add(imei);
            }
        }
    }

    if (totalVenta.value <= 0) {
        Swal.fire({
            title: 'Error',
            text: 'El total de la venta debe ser mayor a Bs 0.00',
            icon: 'error',
            toast: true,
            position: 'center',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        return;
    }

    // Preparar los datos para enviar
    const ventaData = {
        ...form.value,
        nuevo_cliente: null
    };

    // Detectar si hay un nuevo cliente basándose en los datos del formulario
    console.log('Debug - form.value.id_cliente:', form.value.id_cliente);
    console.log('Debug - nuevoCliente.value:', nuevoCliente.value);
    console.log('Debug - nuevoCliente.value.nombre:', nuevoCliente.value.nombre);

    // Detectar si hay un nuevo cliente basándose en la bandera
    if (!isClienteExistente.value && nuevoCliente.value.nombre && nuevoCliente.value.nombre.trim()) {
        ventaData.nuevo_cliente = {
            nombre: nuevoCliente.value.nombre.trim(),
            apellidos: (nuevoCliente.value.apellidos || '').trim(),
            ci: (nuevoCliente.value.ci || '').trim(),
            telefono: (nuevoCliente.value.telefono || '').trim()
        };
        // Limpiar el ID temporal
        ventaData.id_cliente = '';
        console.log('Debug - Enviando nuevo_cliente:', ventaData.nuevo_cliente);
    } else {
        console.log('Debug - Cliente existente, no se envía nuevo_cliente');
    }

    console.log('Debug - ventaData final:', ventaData);
    console.log('=== ENVIANDO DATOS ===');

    router.post(route('ventas.store'), ventaData);
};

const formatCurrency = (amount: number) => {
    const numAmount = Number(amount) || 0;
    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(numAmount);
};

const validateDate = () => {
    const selectedDate = new Date(form.value.fecha);
    const today = new Date();
    today.setHours(23, 59, 59, 999); // Final del día de hoy

    if (selectedDate > today) {
        form.value.fecha = getTodayDate(); // Resetear a fecha de hoy
        // Mostrar mensaje de error
        clientErrors.value.fecha = 'No se pueden seleccionar fechas futuras';

        // Limpiar el error después de 3 segundos
        setTimeout(() => {
            if (clientErrors.value.fecha) {
                delete clientErrors.value.fecha;
            }
        }, 3000);
    } else {
        // Limpiar error si la fecha es válida
        if (clientErrors.value.fecha) {
            delete clientErrors.value.fecha;
        }
    }
};

// Función para verificar si un producto es un smartphone
const isSmartphone = (producto: Producto | null) => {
    return producto?.categoria?.nombre?.toLowerCase() === 'celulares';
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
        Swal.fire({
            title: 'Cantidad limitada',
            text: 'Los celulares solo pueden venderse de uno en uno debido al IMEI único.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        updateTotal(index);
        return;
    }

    // Validar stock disponible
    if (producto && cantidadNum > producto.stock_disponible) {
        form.value.productos[index].cantidad = producto.stock_disponible;
        Swal.fire({
            title: 'Stock insuficiente',
            text: `Solo hay ${producto.stock_disponible} unidades disponibles de este producto.`,
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
        updateTotal(index);
        return;
    }

    // Si todo está bien, actualizar la cantidad
    form.value.productos[index].cantidad = cantidadNum;
    updateTotal(index);
};

// Validación en tiempo real de IMEI (vacío/duplicado en el formulario)
const validateImeiInline = (index: number) => {
    const productoInfo = selectedProductos.value[index];
    if (!isSmartphone(productoInfo)) {
        imeiErrors.value[index] = '';
        return;
    }
    const imei = (form.value.productos[index]?.descripcion || '').trim();
    if (!imei) {
        imeiErrors.value[index] = 'Ingrese el IMEI del celular';
        return;
    }
    // verificar duplicidad dentro del formulario
    const imeiLower = imei.toLowerCase();
    for (let i = 0; i < form.value.productos.length; i++) {
        if (i === index) continue;
        const otherInfo = selectedProductos.value[i];
        if (!isSmartphone(otherInfo)) continue;
        const otherImei = (form.value.productos[i]?.descripcion || '').trim().toLowerCase();
        if (otherImei && otherImei === imeiLower) {
            imeiErrors.value[index] = `El IMEI ${imei} ya está agregado en esta venta`;
            return;
        }
    }
    imeiErrors.value[index] = '';
};

onMounted(() => {
    // Inicializar filtros
    filteredClientes.value = props.clientes;

    // Verificar si hay productos seleccionados desde la interfaz de selección
    const productosSeleccionados = sessionStorage.getItem('productos_seleccionados');

    if (productosSeleccionados) {
        try {
            const productos = JSON.parse(productosSeleccionados);

            // Limpiar productos existentes
            form.value.productos = [];
            selectedProductos.value = [];
            productoSearch.value = [];
            showProductosDropdown.value = [];
            filteredProductos.value = [];

            // Agregar productos seleccionados
            productos.forEach((producto: any, index: number) => {
                form.value.productos.push({
                    id_producto: producto.id_producto,
                    cantidad: 1,
                    precio_unitario: producto.precio_venta || 0,
                    descripcion: '',
                    total_parcial: producto.precio_venta || 0
                });

                selectedProductos.value.push(producto);
                const nombreProducto = producto.nombre || producto.modelo?.nombre || '';
                productoSearch.value[index] = nombreProducto;
                showProductosDropdown.value[index] = false;
                filteredProductos.value[index] = [];
            });

            // Limpiar sessionStorage
            sessionStorage.removeItem('productos_seleccionados');
        } catch (error) {
            console.error('Error al cargar productos seleccionados:', error);
            addProducto();
        }
    } else {
        // Verificar si hay datos de venta temporal para recuperar
        const ventaTemporal = props.venta_edicion;

    if (ventaTemporal) {

        // Recuperar datos del cliente
        console.log('Datos de venta temporal recibidos:', ventaTemporal);
        console.log('¿Tiene nuevo_cliente?', !!ventaTemporal.nuevo_cliente);
        console.log('¿Tiene cliente?', !!ventaTemporal.cliente);

        if (ventaTemporal.nuevo_cliente) {
            // Es un cliente nuevo temporal
            console.log('Cliente nuevo encontrado:', ventaTemporal.nuevo_cliente);
            showNuevoCliente.value = true;
            clienteSearch.value = ventaTemporal.nuevo_cliente.nombre || '';
            nuevoCliente.value = {
                nombre: ventaTemporal.nuevo_cliente.nombre || '',
                apellidos: ventaTemporal.nuevo_cliente.apellidos || '',
                ci: ventaTemporal.nuevo_cliente.ci || '',
                telefono: ventaTemporal.nuevo_cliente.telefono || ''
            };
            console.log('Datos del cliente asignados:', nuevoCliente.value);
            console.log('clienteSearch.value:', clienteSearch.value);
        } else if (ventaTemporal.cliente) {
            if (ventaTemporal.cliente.es_temporal) {
                // Es un cliente nuevo temporal (formato anterior)
                showNuevoCliente.value = true;
                clienteSearch.value = ventaTemporal.cliente.nombre;
                nuevoCliente.value = {
                    nombre: ventaTemporal.cliente.nombre,
                    apellidos: ventaTemporal.cliente.apellidos || '',
                    ci: ventaTemporal.cliente.ci || '',
                    telefono: ventaTemporal.cliente.telefono || ''
                };
            } else {
                // Es un cliente existente
                form.value.id_cliente = ventaTemporal.cliente.id_cliente;
                clienteSearch.value = ventaTemporal.cliente.nombre;

                // Llenar campos con datos del cliente existente para mostrar
                Object.assign(nuevoCliente.value, {
                    nombre: ventaTemporal.cliente.nombre,
                    apellidos: ventaTemporal.cliente.apellidos || '',
                    ci: ventaTemporal.cliente.ci || '',
                    telefono: ventaTemporal.cliente.telefono || ''
                });

                // Marcar como cliente existente
                isClienteExistente.value = true;
                selectedCliente.value = ventaTemporal.cliente;

                console.log('Cliente existente recuperado, campos llenados:', nuevoCliente.value);
            }
        }

        // Recuperar fecha
        if (ventaTemporal.fecha) {
            form.value.fecha = ventaTemporal.fecha;
        }

        // Limpiar productos existentes
        form.value.productos = [];
        selectedProductos.value = [];
        productoSearch.value = [];

        // Recuperar productos
        if (ventaTemporal.detalles && ventaTemporal.detalles.length > 0) {
            ventaTemporal.detalles.forEach((detalle: any, index: number) => {
                if (detalle.producto) {
                    // Agregar producto al formulario
                    form.value.productos.push({
                        id_producto: detalle.id_producto,
                        cantidad: detalle.cantidad,
                        precio_unitario: detalle.precio_unitario,
                        descripcion: detalle.imei || detalle.descripcion || '',
                        total_parcial: detalle.total_parcial
                    });

                    // Agregar producto a la lista de productos seleccionados
                    selectedProductos.value.push(detalle.producto);

                    // Poblar el campo de búsqueda con el nombre del producto
                    const nombreProducto = detalle.producto.nombre || detalle.producto.modelo?.nombre || '';
                    productoSearch.value[index] = nombreProducto;
                }
            });
        }
    } else {
        // Agregar un producto por defecto si no hay datos temporales
        addProducto();
    }
    }

    // Agregar listener global para clicks fuera del dropdown
    document.addEventListener('click', handleClickOutside);

    // Mostrar aviso flotante si el backend devolvió error de IMEI
    if (props.errors && Object.keys(props.errors).length > 0) {
        const values = Object.values(props.errors || {});
        const imeiError = values.find(v => typeof v === 'string' && v.toLowerCase().includes('imei')) as string | undefined;
        if (imeiError) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Este IMEI ya fue registrado',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    }
});

// Watcher para actualizar el nombre del cliente en tiempo real
watch(clienteSearch, (newValue) => {
    if (newValue.trim() === '') {
        clearClienteData();
    } else if (!selectedCliente.value) {
        // Si no hay cliente seleccionado, actualizar el nombre
        nuevoCliente.value.nombre = newValue.trim();
        console.log('Debug - Watcher actualizando nombre:', nuevoCliente.value.nombre);
    }
});

onUnmounted(() => {
    // Remover listener global al desmontar el componente
    document.removeEventListener('click', handleClickOutside);
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
