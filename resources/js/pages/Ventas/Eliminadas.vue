<template>
    <AppLayout>
        <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628] py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Ventas Anuladas</h1>
                        <p class="text-blue-300 text-sm">Historial de ventas anuladas del sistema</p>
                    </div>
                    <Link
                        :href="route('ventas.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a Ventas
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
                            placeholder="Buscar por cliente, producto o descripción..."
                            class="w-full pl-10 pr-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            @input="debounceSearch"
                        />
                    </div>
                </div>

                <!-- Tabla de Ventas Anuladas -->
                <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-black/30">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Anulada</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-500/20">
                                <tr
                                    v-for="venta in filteredVentas"
                                    :key="venta.id_venta"
                                    class="hover:bg-blue-500/10 transition-colors"
                                >
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-white">
                                        #{{ venta.id_venta }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-blue-300">
                                        {{ formatDate(venta.fecha) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-white">
                                            {{ venta.cliente ? `${venta.cliente.nombre || ''} ${venta.cliente.apellidos || ''}`.trim() : 'Sin cliente' }}
                                        </div>
                                        <div class="text-sm text-blue-300">{{ venta.cliente?.telefono || 'Sin teléfono' }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-green-400 font-semibold">
                                        Bs. {{ venta.total?.toLocaleString() || '0' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-red-400">
                                        {{ formatDate(venta.fecha_eliminacion) }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                        <Link
                                            :href="route('ventas.show', venta.id_venta)"
                                            class="text-blue-400 hover:text-blue-300 transition-colors"
                                        >
                                            Ver Detalles
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje cuando no hay ventas -->
                    <div v-if="filteredVentas.length === 0" class="text-center py-12">
                        <div class="text-gray-400 text-lg mb-2">No se encontraron ventas anuladas</div>
                        <div class="text-blue-300 text-sm">Intenta ajustar los filtros de búsqueda</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface Cliente {
    nombre: string;
    apellidos?: string;
    telefono?: string;
}

interface Venta {
    id_venta: number;
    fecha: string;
    total: number;
    fecha_eliminacion: string;
    cliente: Cliente;
}

const props = defineProps<{
    ventas: Venta[];
}>();

const search = ref('');

// Filtrar ventas basado en la búsqueda
const filteredVentas = computed(() => {
    if (!search.value.trim()) {
        return props.ventas;
    }

    const searchTerm = search.value.toLowerCase();
    return props.ventas.filter(venta => {
        const clienteNombre = venta.cliente?.nombre?.toLowerCase() || '';
        const clienteApellidos = venta.cliente?.apellidos?.toLowerCase() || '';
        const nombreCompleto = `${clienteNombre} ${clienteApellidos}`.trim();
        const clienteTelefono = venta.cliente?.telefono?.toLowerCase() || '';

        return nombreCompleto.includes(searchTerm) ||
               clienteNombre.includes(searchTerm) ||
               clienteApellidos.includes(searchTerm) ||
               clienteTelefono.includes(searchTerm) ||
               venta.id_venta.toString().includes(searchTerm);
    });
});

// Debounce para la búsqueda
let searchTimeout: NodeJS.Timeout;
const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // La búsqueda se maneja automáticamente por el computed
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
</script>
