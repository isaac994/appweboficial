<template>
    <AppLayout title="Compras">
        <template #header>
            <Heading>Gestión de Compras</Heading>
        </template>

        <div class="space-y-6">
            <!-- Filtros -->
            <Card>
                <CardHeader>
                    <CardTitle>Filtros</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <Label for="search">Buscar por proveedor</Label>
                            <Input
                                id="search"
                                v-model="filters.search"
                                placeholder="Nombre del proveedor..."
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label for="proveedor">Proveedor</Label>
                            <select
                                id="proveedor"
                                v-model="filters.proveedor"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Todos los proveedores</option>
                                <option
                                    v-for="proveedor in proveedores"
                                    :key="proveedor.id_proveedor"
                                    :value="proveedor.id_proveedor"
                                >
                                    {{ proveedor.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <Label for="fecha_desde">Fecha desde</Label>
                            <Input
                                id="fecha_desde"
                                v-model="filters.fecha_desde"
                                type="date"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label for="fecha_hasta">Fecha hasta</Label>
                            <Input
                                id="fecha_hasta"
                                v-model="filters.fecha_hasta"
                                type="date"
                                class="mt-1"
                            />
                        </div>
                        <div class="md:col-span-4 flex gap-2">
                            <Button type="submit" variant="default">
                                Aplicar Filtros
                            </Button>
                            <Button type="button" variant="outline" @click="clearFilters">
                                Limpiar Filtros
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <!-- Tabla de Compras -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle>Lista de Compras</CardTitle>
                        <CardDescription>
                            Gestiona todas las compras realizadas a proveedores
                        </CardDescription>
                    </div>
                    <Button @click="router.visit(route('compras.create'))">
                        Nueva Compra
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="compras.data && compras.data.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">ID</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Proveedor</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Fecha</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Total</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Usuario</th>
                                    <th class="text-left py-3 px-4 font-medium text-gray-900">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="compra in compras.data"
                                    :key="compra.id_compra"
                                    class="border-b border-gray-100 hover:bg-gray-50"
                                >
                                    <td class="py-3 px-4 text-gray-900 font-mono">
                                        #{{ compra.id_compra }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900">
                                            {{ compra.proveedor?.nombre }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ formatDate(compra.fecha) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-green-600">
                                            ${{ formatCurrency(compra.total) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ compra.usuario?.name }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2">
                                            <Button
                                                @click="router.visit(route('compras.show', compra.id_compra))"
                                                variant="ghost"
                                                size="sm"
                                            >
                                                Ver
                                            </Button>
                                            <Button
                                                @click="router.visit(route('compras.edit', compra.id_compra))"
                                                variant="ghost"
                                                size="sm"
                                            >
                                                Editar
                                            </Button>
                                            <Button
                                                @click="deleteCompra(compra.id_compra)"
                                                variant="ghost"
                                                size="sm"
                                                class="text-red-600 hover:text-red-700"
                                            >
                                                Eliminar
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje cuando no hay compras -->
                    <div v-else class="text-center py-8 text-gray-500">
                        <p>No hay compras registradas</p>
                        <p class="text-sm">Haga clic en "Nueva Compra" para comenzar</p>
                    </div>

                    <!-- Paginación -->
                    <div v-if="compras.links && compras.links.length > 3" class="mt-6">
                        <nav class="flex justify-center">
                            <div class="flex space-x-1">
                                <template v-for="(link, key) in compras.links" :key="key">
                                    <!-- Enlaces habilitados -->
                                    <Link
                                        v-if="link.url !== null"
                                        :href="link.url"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md',
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                                        ]"
                                        v-html="link.label"
                                    />
                                    <!-- Enlaces deshabilitados -->
                                    <span
                                        v-else
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-md text-gray-400 cursor-not-allowed'
                                        ]"
                                        v-html="link.label"
                                    />
                                </template>
                            </div>
                        </nav>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Compra {
    id_compra: number;
    fecha: string;
    total: number;
    proveedor: {
        nombre: string;
    };
    usuario: {
        name: string;
    };
}

interface Proveedor {
    id_proveedor: number;
    nombre: string;
}

interface CompraPaginated {
    data: Compra[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Filters {
    search: string;
    proveedor: string;
    fecha_desde: string;
    fecha_hasta: string;
}

const props = defineProps<{
    compras: CompraPaginated;
    proveedores: Proveedor[];
    filters?: Filters;
}>();

const filters = ref<Filters>({
    search: props.filters?.search || '',
    proveedor: props.filters?.proveedor || '',
    fecha_desde: props.filters?.fecha_desde || '',
    fecha_hasta: props.filters?.fecha_hasta || ''
});

// Asegurar que siempre tengamos un objeto válido
if (!props.filters) {
    filters.value = {
        search: '',
        proveedor: '',
        fecha_desde: '',
        fecha_hasta: ''
    };
}

const applyFilters = () => {
    router.get(route('compras.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        proveedor: '',
        fecha_desde: '',
        fecha_hasta: ''
    };
    applyFilters();
};

const deleteCompra = (id: number) => {
    if (confirm('¿Estás seguro de que quieres eliminar esta compra?')) {
        router.delete(route('compras.destroy', id));
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};



const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};
</script>
