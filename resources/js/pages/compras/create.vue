<template>
    <AppLayout title="Nueva Compra">
        <template #header>
            <Heading>Nueva Compra</Heading>
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
            <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-blue-500/30 p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-white mb-2">Información de la Compra</h2>
                    <p class="text-gray-300">Complete los datos para registrar una nueva compra</p>
                </div>
                <div>
                    <form @submit.prevent class="space-y-6">

                        <!-- Información básica -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Proveedor -->
                            <div class="relative">
                                <Label for="proveedor" class="text-gray-300">Proveedor *</Label>
                                <div class="relative">
                                    <Input
                                        id="proveedor"
                                        v-model="proveedorSearch"
                                        type="text"
                                        placeholder="Buscar proveedor..."
                                        class="mt-1 pr-10 text-white bg-black/30 border-blue-500/50"
                                        :class="{
                                            'border-red-500': errors.id_proveedor && !showNuevoProveedor,
                                                'border-green-400 bg-black/40': selectedProveedor
                                        }"
                                        @input="filterProveedores"
                                        @focus="showProveedoresDropdown = true"
                                        @blur="handleProveedorBlur"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                            </div>

                            <!-- Dropdown de proveedores -->
                            <div v-if="showProveedoresDropdown && filteredProveedores.length > 0"
                                     class="absolute z-10 w-full mt-1 bg-[#0a1628] border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                                    <div
                                        v-for="proveedor in filteredProveedores"
                                        :key="proveedor.id_proveedor"
                                        @mousedown="selectProveedor(proveedor)"
                                        class="px-4 py-2 hover:bg-blue-600/20 cursor-pointer border-b border-blue-500/20 last:border-b-0"
                                    >
                                        <div class="font-medium text-white">{{ proveedor.nombre }}</div>
                                        <div class="text-sm text-gray-400">{{ proveedor.ci_nit }}</div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="errors.id_proveedor && !showNuevoProveedor" class="mt-1 text-sm text-red-600">
                                {{ errors.id_proveedor }}
                            </div>

                            <!-- Fecha -->
                            <div>
                                <Label for="fecha" class="text-gray-300">Fecha *</Label>
                                <div class="relative">
                                    <Input
                                        id="fecha"
                                        v-model="form.fecha"
                                        type="text"
                                        readonly
                                        required
                                        class="mt-1 text-white bg-black/30 border-blue-500/50 cursor-pointer"
                                        :class="{ 'border-red-500': errors.fecha }"
                                        @click="showCalendar = !showCalendar"
                                        @keyup.enter.prevent="focusPrimerProducto"
                                        :value="formatDateDisplay(form.fecha)"
                                        placeholder="Seleccionar fecha"
                                    />
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 pointer-events-none">
                                        <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Calendario personalizado -->
                                <div v-if="showCalendar" class="absolute z-50 mt-1 bg-gradient-to-br from-blue-900/95 to-cyan-900/95 backdrop-blur-xl border border-blue-500/50 rounded-xl p-4 shadow-2xl">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-white font-semibold">{{ currentMonthYear }}</h3>
                                        <div class="flex gap-2">
                                            <button @click="previousMonth" class="p-1 text-blue-400 hover:text-white hover:bg-blue-600/30 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                                </svg>
                                            </button>
                                            <button @click="nextMonth" class="p-1 text-blue-400 hover:text-white hover:bg-blue-600/30 rounded">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1 mb-2">
                                        <div v-for="day in ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa', 'Do']" :key="day" class="text-center text-blue-300 text-sm font-medium py-2">
                                            {{ day }}
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-7 gap-1">
                                        <button
                                            v-for="day in calendarDays"
                                            :key="day.date"
                                            @click="selectDate(day.date)"
                                            :class="[
                                                'p-2 text-sm rounded-lg transition-colors',
                                                day.isCurrentMonth ? 'text-white hover:bg-blue-600/50' : 'text-blue-300/50',
                                                day.isSelected ? 'bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold' : '',
                                                day.isToday ? 'ring-2 ring-blue-400' : ''
                                            ]"
                                        >
                                            {{ day.day }}
                                        </button>
                                    </div>

                                    <div class="flex justify-between mt-4 pt-3 border-t border-blue-500/30">
                                        <button @click="clearDate" class="text-blue-400 hover:text-white text-sm">Borrar</button>
                                        <button @click="selectToday" class="text-blue-400 hover:text-white text-sm">Hoy</button>
                                    </div>
                                </div>

                                <p v-if="errors.fecha" class="mt-1 text-sm text-red-600">
                                    {{ errors.fecha }}
                                </p>
                            </div>
                        </div>

                        <!-- Formulario para nuevo proveedor -->
                        <div class="mt-4 p-4 bg-black/30 border border-blue-500/30 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label class="text-blue-300">CI/NIT *</Label>
                                    <Input
                                        v-model="nuevoProveedor.ci_nit"
                                        placeholder="CI/NIT"
                                        class="mt-1 text-white bg-black/30 border-blue-500/50"
                                    />
                                </div>
                                <div>
                                    <Label class="text-blue-300">Teléfono *</Label>
                                    <Input
                                        v-model="nuevoProveedor.telefono"
                                        placeholder="Teléfono"
                                        class="mt-1 text-white bg-black/30 border-blue-500/50"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium">Productos</h3>
                                <Button
                                    type="button"
                                    @click="addProduct"
                                    variant="outline"
                                    size="sm"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Agregar Producto
                                </Button>
                            </div>

                            <div v-if="form.productos.length === 0" class="text-center py-8 text-gray-300">
                                <p>No hay productos agregados</p>
                                <p class="text-sm">Haga clic en "Agregar Producto" para comenzar</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="(producto, index) in form.productos"
                                    :key="index"
                                    class="border border-blue-500/30 bg-black/20 rounded-lg p-4"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="font-medium text-white">Producto {{ index + 1 }}</h4>
                                        <Button
                                            type="button"
                                            @click="removeProduct(index)"
                                            variant="ghost"
                                            size="sm"
                                            class="text-red-400 hover:text-red-300 hover:bg-red-600/20"
                                        >
                                            Anular
                                        </Button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <!-- Producto -->
                                        <div class="relative">
                                            <Label :for="`producto_${index}`" class="text-gray-300">Producto *</Label>
                                            <div class="relative">
                                                <Input
                                                    :id="`producto_${index}`"
                                                    v-model="productoSearch[index]"
                                                    type="text"
                                                    placeholder="Buscar producto..."
                                                    class="mt-1 pr-10 text-white bg-black/30 border-blue-500/50"
                                                    :class="{
                                                        'border-blue-400 bg-black/40': selectedProductos[index]
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

                                        <!-- Cantidad -->
                                        <div>
                                            <Label :for="`cantidad_${index}`" class="text-gray-300">Cantidad *</Label>
                                            <Input
                                                :id="`cantidad_${index}`"
                                                v-model.number="producto.cantidad"
                                                type="number"
                                                min="1"
                                                required
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                @keyup.enter.prevent="focusPrecio(index)"
                                            />
                                        </div>

                                        <!-- Precio Unitario -->
                                        <div>
                                            <Label :for="`precio_${index}`" class="text-gray-300">Precio Unitario *</Label>
                                            <Input
                                                :id="`precio_${index}`"
                                                v-model.number="producto.precio_unitario"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                required
                                                class="mt-1 text-white bg-black/30 border-blue-500/50"
                                                @keyup.enter.prevent="focusSiguienteProducto(index)"
                                            />
                                        </div>

                                        <!-- Total Parcial -->
                                        <div>
                                            <Label :for="`total_${index}`">Total Parcial</Label>
                                            <Input
                                                :id="`total_${index}`"
                                                :value="formatCurrency(producto.cantidad * producto.precio_unitario)"
                                                readonly
                                                class="mt-1 bg-gray-50"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t pt-4">
                            <div class="flex justify-end">
                                <div class="text-right">
                                    <div class="text-lg font-semibold" :class="{ 'text-red-500': totalCompra <= 0 }">
                                        Total: {{ formatCurrency(totalCompra) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit('/compras')"
                                class="focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-0"
                            >
                                Cancelar
                            </Button>
                            <Button
                                id="btn-crear-compra"
                                type="button"
                                :disabled="isSubmitting || totalCompra <= 0 || form.productos.length === 0"
                                @click="submitForm"
                                class="focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-0"
                            >
                                {{ isSubmitting ? 'Creando...' : 'Crear Compra' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Swal from 'sweetalert2';

interface Producto {
    id_producto: number;
    nombre: string;
    descripcion: string;
    precio_venta: string;
    id_categoria: number;
    id_marca: number;
    categoria?: {
        nombre: string;
    };
    marca?: {
        nombre: string;
    };
}

interface Proveedor {
    id_proveedor: number;
    nombre: string;
    ci_nit: string;
    telefono: string;
}

interface Props {
    proveedores: Proveedor[];
    productos: Producto[];
    errors: Record<string, string>;
}

const props = defineProps<Props>();

// Formulario principal
const form = useForm({
    fecha: '',
    id_proveedor: '',
    productos: [] as Array<{
        id_producto: number;
        cantidad: number;
        precio_unitario: number;
    }>,
    nuevo_proveedor: null as {
        ci_nit: string;
        telefono: string;
    } | null
});

// Estados para proveedores
const proveedorSearch = ref('');
const selectedProveedor = ref<Proveedor | null>(null);
const showProveedoresDropdown = ref(false);
const filteredProveedores = ref<Proveedor[]>([]);

// Estados para nuevo proveedor
const showNuevoProveedor = ref(false);
const nuevoProveedor = ref({
    ci_nit: '',
    telefono: ''
});

// Estados para productos
const productoSearch = ref<string[]>([]);
const selectedProductos = ref<(Producto | null)[]>([]);
const showProductosDropdown = ref<boolean[]>([]);
const filteredProductos = ref<Producto[][]>([]);

// Estados del formulario
const isSubmitting = ref(false);

// Estados para el calendario personalizado
const showCalendar = ref(false);
const currentDate = ref(new Date());

// Computed
const totalCompra = computed(() => {
    return form.productos.reduce((total, producto) => {
        return total + (producto.cantidad * producto.precio_unitario);
    }, 0);
});

// Computed para el calendario
const currentMonthYear = computed(() => {
    return currentDate.value.toLocaleDateString('es-BO', {
        month: 'long',
        year: 'numeric'
    });
});

const calendarDays = computed(() => {
    const year = currentDate.value.getFullYear();
    const month = currentDate.value.getMonth();
    const today = new Date();
    const selectedDate = form.fecha ? new Date(form.fecha) : null;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay() + 1);

    const days = [];
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const isCurrentMonth = date.getMonth() === month;
        const isToday = date.toDateString() === today.toDateString();
        const isSelected = selectedDate && date.toDateString() === selectedDate.toDateString();

        days.push({
            day: date.getDate(),
            date: date.toISOString().split('T')[0],
            isCurrentMonth,
            isToday,
            isSelected
        });
    }

    return days;
});

// Funciones de utilidad
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB'
    }).format(amount).replace('BOB', 'Bs');
};

const getTodayDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Funciones del calendario personalizado
const formatDateDisplay = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-BO');
};

const selectDate = (dateString: string) => {
    form.fecha = dateString;
    showCalendar.value = false;
};

const clearDate = () => {
    form.fecha = '';
    showCalendar.value = false;
};

const selectToday = () => {
    form.fecha = getTodayDate();
    showCalendar.value = false;
};

const previousMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1);
};

// Funciones para proveedores
const filterProveedores = () => {
    if (!proveedorSearch.value.trim()) {
        filteredProveedores.value = props.proveedores;
    } else {
        const search = proveedorSearch.value.toLowerCase();
        filteredProveedores.value = props.proveedores.filter(proveedor =>
            proveedor.nombre.toLowerCase().includes(search) ||
            proveedor.ci_nit.toLowerCase().includes(search)
        );
    }
    showProveedoresDropdown.value = true;
};

const selectProveedor = (proveedor: Proveedor) => {
    selectedProveedor.value = proveedor;
    form.id_proveedor = proveedor.id_proveedor.toString();
    proveedorSearch.value = proveedor.nombre;

    // Llenar los campos de CI/NIT y Teléfono con los datos del proveedor seleccionado
    nuevoProveedor.value.ci_nit = proveedor.ci_nit;
    nuevoProveedor.value.telefono = proveedor.telefono;

    showProveedoresDropdown.value = false;

    // Después de seleccionar, ir al campo fecha
    setTimeout(() => {
        focusFecha();
    }, 100);
};

const handleProveedorFocus = () => {
    showProveedoresDropdown.value = true;
    if (selectedProveedor.value) {
        // Si ya hay un proveedor seleccionado, limpiar la búsqueda para permitir nueva búsqueda
        proveedorSearch.value = '';
        selectedProveedor.value = null;
        form.id_proveedor = '';
    }
};

const handleProveedorBlur = () => {
    setTimeout(() => {
        showProveedoresDropdown.value = false;
    }, 300);
};

// Funciones para nuevo proveedor
const closeNuevoProveedor = () => {
    showNuevoProveedor.value = false;
    nuevoProveedor.value = {
        ci_nit: '',
        telefono: ''
    };
    form.nuevo_proveedor = null;
};

// Funciones para productos
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
};

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
    form.productos[index].id_producto = producto.id_producto;
    // No llenar automáticamente el precio unitario
    productoSearch.value[index] = producto.modelo?.nombre || 'Sin modelo';
    showProductosDropdown.value[index] = false;

    // Después de seleccionar, ir al campo cantidad
    setTimeout(() => {
        focusCantidad(index);
    }, 100);
};

const handleProductoFocus = (index: number) => {
    showProductosDropdown.value[index] = true;
    if (selectedProductos.value[index]) {
        // Si ya hay un producto seleccionado, limpiar la búsqueda para permitir nueva búsqueda
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

// Funciones de navegación con Enter
const focusFecha = () => {
    console.log('🎯 focusFecha ejecutado');
    const fechaInput = document.getElementById('fecha');
    if (fechaInput) {
        fechaInput.focus();
        console.log('✅ Focus en fecha aplicado');
    } else {
        console.log('❌ No se encontró el campo fecha');
    }
};

const focusPrimerProducto = () => {
    console.log('🎯 focusPrimerProducto ejecutado');
    const primerProducto = document.getElementById('producto_0');
    if (primerProducto) {
        primerProducto.focus();
        console.log('✅ Focus en primer producto aplicado');
    } else {
        console.log('⚠️ No hay productos, agregando uno...');
        // Si no hay productos, agregar uno
        addProduct();
        setTimeout(() => {
            const productoInput = document.getElementById('producto_0');
            if (productoInput) {
                productoInput.focus();
                console.log('✅ Focus en nuevo producto aplicado');
            }
        }, 100);
    }
};

const focusCantidad = (index: number) => {
    console.log('🎯 focusCantidad ejecutado para index:', index);
    const cantidadInput = document.getElementById(`cantidad_${index}`);
    if (cantidadInput) {
        cantidadInput.focus();
        console.log('✅ Focus en cantidad aplicado');
    } else {
        console.log('❌ No se encontró el campo cantidad');
    }
};

const focusPrecio = (index: number) => {
    console.log('🎯 focusPrecio ejecutado para index:', index);
    const precioInput = document.getElementById(`precio_${index}`);
    if (precioInput) {
        precioInput.focus();
        console.log('✅ Focus en precio aplicado');
    } else {
        console.log('❌ No se encontró el campo precio');
    }
};

const focusSiguienteProducto = (index: number) => {
    console.log('🎯 focusSiguienteProducto ejecutado para index:', index);
    const siguienteIndex = index + 1;
    const siguienteProducto = document.getElementById(`producto_${siguienteIndex}`);

    if (siguienteProducto) {
        console.log('✅ Hay siguiente producto, yendo a él...');
        siguienteProducto.focus();
        console.log('✅ Focus en siguiente producto aplicado');
    } else {
        console.log('⚠️ Es el último producto, yendo al botón crear...');
        // Si es el último producto, ir al botón crear
        focusBotonCrear();
    }
};

const focusBotonCrear = () => {
    console.log('🎯 focusBotonCrear ejecutado');
    const botonCrear = document.getElementById('btn-crear-compra') as HTMLButtonElement;
    if (botonCrear) {
        botonCrear.focus();
        console.log('✅ Focus en botón crear aplicado');
    } else {
        console.log('❌ No se encontró el botón crear');
    }
};


// Función de envío
const submitForm = () => {
    console.log('🚀 SUBMIT FORM INICIADO - ESTO NO DEBE PASAR AUTOMÁTICAMENTE');
    console.log('Form data:', {
        fecha: form.fecha,
        id_proveedor: form.id_proveedor,
        productos: form.productos,
        showNuevoProveedor: showNuevoProveedor.value,
        nuevoProveedor: nuevoProveedor.value
    });

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

    isSubmitting.value = true;

    const formData = {
        fecha: form.fecha,
        id_proveedor: form.id_proveedor,
        productos: form.productos
    };

    // Si se está creando un nuevo proveedor (hay texto pero no hay proveedor seleccionado)
    if (!selectedProveedor.value && proveedorSearch.value && nuevoProveedor.value.ci_nit && nuevoProveedor.value.telefono) {
        formData.nuevo_proveedor = {
            nombre: proveedorSearch.value, // Tomar el nombre del campo de búsqueda
            ci_nit: nuevoProveedor.value.ci_nit,
            telefono: nuevoProveedor.value.telefono
        };
        formData.id_proveedor = '';
    }

    console.log('📤 Enviando datos:', formData);

    // Guardar datos temporalmente y redirigir a confirmación
    router.post('/compras/confirmar', formData, {
        onError: (errors) => {
            console.log('❌ Errores:', errors);
            isSubmitting.value = false;
        }
    });
};

// Inicialización
onMounted(() => {
    form.fecha = getTodayDate();
    filteredProveedores.value = props.proveedores;

    // Verificar si hay productos seleccionados desde la interfaz de selección
    const productosSeleccionados = sessionStorage.getItem('productos_compra');

    if (productosSeleccionados) {
        try {
            const productos = JSON.parse(productosSeleccionados);

            // Limpiar productos existentes
            form.productos = [];
            selectedProductos.value = [];
            productoSearch.value = [];
            showProductosDropdown.value = [];

            // Agregar productos seleccionados
            productos.forEach((producto: any) => {
                const index = form.productos.length;
                form.productos.push({
                    id_producto: producto.id_producto,
                    cantidad: 1,
                    precio_unitario: 0,
                    descripcion: ''
                });

                selectedProductos.value.push(producto);
                const nombreProducto = producto.nombre || producto.modelo?.nombre || '';
                productoSearch.value.push(nombreProducto);
                showProductosDropdown.value.push(false);
            });

            // Limpiar sessionStorage
            sessionStorage.removeItem('productos_compra');
        } catch (error) {
            console.error('Error al cargar productos seleccionados:', error);
            addProduct(); // Agregar un producto por defecto si hay error
        }
    } else {
        addProduct(); // Agregar un producto por defecto
    }

    // Auto-focus en el campo proveedor al cargar la página
    setTimeout(() => {
        const proveedorInput = document.getElementById('proveedor');
        if (proveedorInput) {
            proveedorInput.focus();
        }
    }, 100);
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
