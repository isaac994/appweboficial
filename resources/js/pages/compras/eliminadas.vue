<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Movimientos</h1>
                        <p class="text-blue-300 text-sm">Historial de compras anuladas del sistema</p>
                    </div>
                    <Link
                        :href="route('compras.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a Compras
                    </Link>
                </div>

                <!-- Búsqueda -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-4 mb-6">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por proveedor, ID..."
                            class="w-full pl-10 pr-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debounceSearch"
                        />
                    </div>
                </div>

                <!-- Tabla de Compras Anuladas -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Proveedor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr
                                    v-for="compra in filteredCompras"
                                    :key="compra.id_compra"
                                    class="hover:bg-blue-500/10 transition-colors"
                                >
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-white">
                                        #{{ compra.id_compra }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-300">
                                        {{ formatDate(compra.fecha) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">{{ compra.proveedor?.nombre || 'Sin proveedor' }}</div>
                                        <div class="text-sm text-blue-300">{{ compra.proveedor?.telefono || 'Sin teléfono' }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-green-400 font-semibold">
                                        Bs. {{ formatCurrency(compra.total) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-red-400">
                                        {{ formatDate(compra.fecha_eliminacion) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <Link
                                                :href="route('compras.show', compra.id_compra)"
                                                class="text-blue-400 hover:text-blue-300 transition-colors"
                                            >
                                                Ver Detalles
                                            </Link>
                                            <button
                                                @click="restaurarCompra(compra.id_compra)"
                                                :disabled="restaurando"
                                                class="text-green-400 hover:text-green-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Restaurar esta compra"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje cuando no hay compras -->
                    <div v-if="filteredCompras.length === 0" class="text-center py-12">
                        <div class="text-gray-400 text-lg mb-2">No se encontraron compras anuladas</div>
                        <div class="text-blue-300 text-sm">Intenta ajustar los filtros de búsqueda</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Proveedor {
    nombre: string;
    telefono?: string;
}

interface Compra {
    id_compra: number;
    fecha: string;
    total: number;
    fecha_eliminacion: string;
    proveedor: Proveedor;
}

const props = defineProps<{
    compras: Compra[];
    search?: string;
}>();

const search = ref(props.search || '');
const restaurando = ref(false);

// Filtrar compras basado en la búsqueda
const filteredCompras = computed(() => {
    if (!search.value.trim()) {
        return props.compras;
    }

    const searchTerm = search.value.toLowerCase();
    return props.compras.filter(compra => {
        const proveedorNombre = compra.proveedor?.nombre?.toLowerCase() || '';
        const proveedorTelefono = compra.proveedor?.telefono?.toLowerCase() || '';

        return proveedorNombre.includes(searchTerm) ||
               proveedorTelefono.includes(searchTerm) ||
               compra.id_compra.toString().includes(searchTerm);
    });
});

// Debounce para la búsqueda
let searchTimeout: NodeJS.Timeout;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('compras.eliminadas'), { search: search.value }, {
            preserveState: true,
            preserveScroll: true
        });
    }, 300);
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'Sin fecha';

    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (amount: number) => {
    return amount.toLocaleString('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const restaurarCompra = (id: number) => {
    if (!confirm('¿Estás seguro de restaurar esta compra? Esto afectará el stock de los productos.')) {
        return;
    }

    restaurando.value = true;
    router.post(route('compras.restaurar', id), {}, {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            restaurando.value = false;
        },
        onError: (errors: any) => {
            restaurando.value = false;
            alert(errors.error || 'Error al restaurar la compra');
        },
        onFinish: () => {
            restaurando.value = false;
        }
    });
};
</script>




