<template>
    <AppLayout title="Nueva Venta">
        <template #header>
            <Heading>Crear Nueva Venta</Heading>
        </template>

        <!-- Error Messages -->
        <div v-if="$page.props.errors && Object.keys($page.props.errors).length > 0" class="mb-6">
          <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h3 class="text-red-400 font-semibold">Error</h3>
            </div>
            <ul class="mt-2 text-red-300 text-sm">
              <li v-for="(error, key) in $page.props.errors" :key="key">{{ error }}</li>
            </ul>
          </div>
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
                            <div class="relative">
                                <Label for="cliente">Cliente *</Label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <Input
                                            id="cliente"
                                            v-model="clienteSearch"
                                            type="text"
                                            placeholder="Buscar cliente..."
                                            class="mt-1 pr-10"
                                            :class="{ 'border-red-500': errors.id_cliente }"
                                            @input="filterClientes"
                                            @focus="showClientesDropdown = true"

                                        />
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <Button
                                        type="button"
                                        @click="showNuevoCliente = true"
                                        variant="outline"
                                        size="sm"
                                        class="mt-1 px-3"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </Button>
                                </div>

                                <!-- Dropdown de clientes -->
                                <div v-if="showClientesDropdown && filteredClientes.length > 0"
                                     class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                    <div
                                        v-for="cliente in filteredClientes"
                                        :key="cliente.id_cliente"
                                        @click="selectCliente(cliente)"
                                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                    >
                                        <div class="font-medium">{{ cliente.nombre }}</div>

                                    </div>
                                </div>

                                <div v-if="errors.id_cliente" class="mt-1 text-sm text-red-600">
                                    {{ errors.id_cliente }}
                                </div>

                                <!-- Formulario para nuevo cliente -->
                                <div v-if="showNuevoCliente" class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded">
                                    <div class="flex items-center gap-2 mb-2">
                                        <Input
                                            v-model="nuevoCliente.apellidos"
                                            type="text"
                                            placeholder="Apellidos"
                                            class="flex-1"
                                            :class="{ 'border-red-500': errors.nuevo_cliente_apellidos }"
                                        />
                                        <Input
                                            v-model="nuevoCliente.ci"
                                            type="text"
                                            placeholder="CI"
                                            class="w-24"
                                            :class="{ 'border-red-500': errors.nuevo_cliente_ci }"
                                        />
                                        <Input
                                            v-model="nuevoCliente.telefono"
                                            type="text"
                                            placeholder="Teléfono"
                                            class="w-32"
                                            :class="{ 'border-red-500': errors.nuevo_cliente_telefono }"
                                        />
                                        <Button
                                            type="button"
                                            @click="showNuevoCliente = false"
                                            variant="ghost"
                                            size="sm"
                                            class="text-gray-500 hover:text-gray-700 px-2"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </Button>
                                    </div>
                                    <div class="text-xs text-gray-500">Nombre: {{ clienteSearch }}</div>
                                </div>
                            </div>

                            <div>
                                <Label for="fecha">Fecha *</Label>
                                <Input
                                    id="fecha"
                                    v-model="form.fecha"
                                    type="date"
                                    :max="todayDate"
                                    class="mt-1"
                                    required
                                    @change="validateDate"
                                />
                                <div v-if="errors?.fecha || clientErrors.fecha" class="mt-1 text-sm text-red-600">
                                    {{ errors?.fecha || clientErrors.fecha }}
                                </div>
                                <p class="mt-1 text-xs text-gray-500">No se pueden seleccionar fechas futuras</p>
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
                                        <div class="relative">
                                            <Label :for="`producto-${index}`">Producto *</Label>
                                            <div class="relative">
                                                <Input
                                                    :id="`producto-${index}`"
                                                    v-model="productoSearch[index]"
                                                    type="text"
                                                    placeholder="Buscar producto..."
                                                    class="mt-1 pr-10"
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
                                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                                <div
                                                    v-for="prod in filteredProductos[index]"
                                                    :key="prod.id_producto"
                                                    @click="selectProducto(index, prod)"
                                                    class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
                                                >
                                                    <div class="font-medium">{{ prod.nombre }}</div>
                                                    <div class="text-sm text-gray-500">{{ prod.categoria?.nombre }} - {{ prod.marca?.nombre }}</div>
                                                    <div class="text-xs text-gray-400">Bs {{ prod.precio_venta }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <Label :for="`cantidad-${index}`">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad-${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                :min="isSmartphone(selectedProductos[index]) ? 1 : 1"
                                                :max="isSmartphone(selectedProductos[index]) ? 1 : selectedProductos[index]?.stock_disponible"
                                                class="mt-1"
                                                :class="{ 'border-red-500': errors[`productos.${index}.cantidad`] }"
                                                required
                                                @input="handleCantidadChange(index, $event.target.value)"
                                            />
                                            <!-- Mostrar stock disponible -->
                                            <div v-if="selectedProductos[index]" class="mt-1 text-sm">
                                                <span class="text-gray-600">Stock disponible: </span>
                                                <span :class="selectedProductos[index].stock_disponible > 0 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                                                    {{ selectedProductos[index].stock_disponible }} unidades
                                                </span>
                                            </div>
                                            <!-- Error de cantidad -->
                                            <div v-if="errors[`productos.${index}.cantidad`]" class="mt-1 text-sm text-red-600">
                                                {{ errors[`productos.${index}.cantidad`] }}
                                            </div>
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
import Swal from 'sweetalert2';

interface Cliente {
    id_cliente: number;
    nombre: string;

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

// Errores de validación del lado del cliente
const clientErrors = ref<Record<string, string>>({});

// Fecha de hoy para restringir fechas futuras
const todayDate = computed(() => getTodayDate());

// Variables para filtrado de clientes
const clienteSearch = ref('');
const showClientesDropdown = ref(false);
const selectedCliente = ref<Cliente | null>(null);
const filteredClientes = ref<Cliente[]>([]);

// Variables para filtrado de productos
const productoSearch = ref<string[]>([]);
const showProductosDropdown = ref<boolean[]>([]);
const selectedProductos = ref<(Producto | null)[]>([]);
const filteredProductos = ref<Producto[][]>([]);

// Variables para nuevo cliente
const showNuevoCliente = ref(false);
const nuevoCliente = ref({
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
};

const removeProducto = (index: number) => {
    form.value.productos.splice(index, 1);

    // Limpiar arrays del producto eliminado
    productoSearch.value.splice(index, 1);
    showProductosDropdown.value.splice(index, 1);
    selectedProductos.value.splice(index, 1);
    filteredProductos.value.splice(index, 1);
};

// Métodos para filtrado de clientes
const filterClientes = () => {
    if (!clienteSearch.value.trim()) {
        filteredClientes.value = props.clientes;
    } else {
        const search = clienteSearch.value.toLowerCase();
        filteredClientes.value = props.clientes.filter(cliente =>
            cliente.nombre.toLowerCase().includes(search)
        );
    }
};

const selectCliente = (cliente: Cliente) => {
    selectedCliente.value = cliente;
    form.value.id_cliente = cliente.id_cliente.toString();
    clienteSearch.value = cliente.nombre;
    showClientesDropdown.value = false;
};



// Métodos para filtrado de productos
const filterProductos = (index: number) => {
    if (!productoSearch.value[index]?.trim()) {
        filteredProductos.value[index] = props.productos;
    } else {
        const search = productoSearch.value[index].toLowerCase();
        filteredProductos.value[index] = props.productos.filter(producto =>
            producto.nombre.toLowerCase().includes(search) ||
            producto.categoria?.nombre.toLowerCase().includes(search) ||
            producto.marca?.nombre.toLowerCase().includes(search)
        );
    }
};

const selectProducto = (index: number, producto: Producto) => {
    selectedProductos.value[index] = producto;
    form.value.productos[index].id_producto = producto.id_producto;
    form.value.productos[index].precio_unitario = producto.precio_venta;
    productoSearch.value[index] = producto.nombre;
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
    producto.total_parcial = producto.cantidad * producto.precio_unitario;
};

const getProductoInfo = (id_producto: number) => {
    return props.productos.find(p => p.id_producto === id_producto);
};





const totalVenta = computed(() => {
    return form.value.productos.reduce((total, producto) => {
        return total + (producto.total_parcial || 0);
    }, 0);
});

const createVenta = () => {
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

    // Validar stock antes de crear la venta
    for (let i = 0; i < form.value.productos.length; i++) {
        const producto = form.value.productos[i];
        const productoInfo = selectedProductos.value[i];

        if (productoInfo && producto.cantidad > productoInfo.stock_disponible) {
            Swal.fire({
                title: 'Stock insuficiente',
                text: `El producto "${productoInfo.nombre}" no tiene suficiente stock. Disponible: ${productoInfo.stock_disponible} unidades.`,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
            return;
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

    // Detectar si hay un nuevo cliente basándose en el formulario abierto y datos
    if (showNuevoCliente.value &&
        clienteSearch.value.trim()) {

        ventaData.nuevo_cliente = {
            nombre: clienteSearch.value.trim(),
            apellidos: nuevoCliente.value.apellidos.trim(),
            ci: nuevoCliente.value.ci.trim(),
            telefono: nuevoCliente.value.telefono.trim()
        };
        // Limpiar el ID temporal
        ventaData.id_cliente = '';
    }

    router.post(route('ventas.store'), ventaData, {
        onSuccess: () => {
            Swal.fire({
                title: '¡Venta Registrada!',
                text: 'La venta se ha registrado exitosamente',
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
        }
    });
};

const formatCurrency = (amount: number) => {
    return 'Bs ' + new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
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
    return producto?.categoria?.nombre?.toLowerCase() === 'smartphones';
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

onMounted(() => {
    // Inicializar filtros
    filteredClientes.value = props.clientes;

    // Agregar un producto por defecto
    addProducto();
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
