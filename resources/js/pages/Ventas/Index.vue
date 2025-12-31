<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Gestión de Ventas</h1>
            <p class="text-blue-300">Consulta todas las ventas y utiliza el buscador para filtrar</p>
          </div>
          <div class="flex gap-3">
            <Link
              :href="route('ventas.eliminadas')"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              Movimientos
            </Link>
            <button
              @click="router.visit(route('ventas.seleccionar-productos'))"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Nueva Venta
            </button>
          </div>
        </div>

        <!-- Filtros y Resumen -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-4 mb-6">
          <!-- Búsqueda -->
          <div class="flex flex-col md:flex-row gap-4 mb-4">
            <!-- Búsqueda Multicampo -->
            <div class="flex-1">
              <label class="block text-xs font-medium text-blue-300 mb-1">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Buscar
              </label>
              <div class="relative">
                <input
                  v-model="busqueda"
                  @input="debounceSearch"
                  type="text"
                  placeholder="Cliente, producto..."
                  class="w-full px-3 py-2 pl-10 bg-black/30 border border-blue-500/50 rounded-lg text-white text-sm placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button
                  v-if="busqueda"
                  @click="clearSearch"
                  class="absolute right-2 top-1/2 transform -translate-y-1/2 text-blue-400 hover:text-white transition-colors"
                  title="Limpiar búsqueda"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

          </div>

        </div>

        <!-- Sales Table -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-black/30">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Cliente</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Productos</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Total</th>
                  <th class="px-6 py-4 text-left text-xs font-medium text-blue-300 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-blue-500/20">
                <template v-for="venta in ventas" :key="venta.id_venta">
                  <tr v-for="(detalle, index) in venta.detalles" :key="`${venta.id_venta}-${detalle.id_detalle_venta}`"
                      class="hover:bg-blue-900/20 transition-colors duration-200">
                    <!-- Cliente (solo en la primera fila de cada venta) -->
                    <td v-if="index === 0" class="px-6 py-4 whitespace-nowrap" :rowspan="venta.detalles.length">
                      <div class="text-sm font-medium text-white">
                        {{ venta.cliente ? `${venta.cliente.nombre} ${venta.cliente.apellidos || ''}`.trim() : 'Sin cliente' }}
                      </div>
                      <div class="text-xs text-blue-300">Venta #{{ venta.id_venta }}</div>
                    </td>

                    <!-- Producto -->
                    <td class="px-6 py-4">
                      <div class="text-sm text-white">
                        {{ (detalle.producto?.marca?.nombre ? detalle.producto.marca.nombre + ' ' : '') + (detalle.producto?.modelo?.nombre || 'Sin modelo') }}
                      </div>
                      <div class="text-xs text-blue-300">Cantidad: {{ detalle.cantidad }}</div>
                      <div class="text-xs text-gray-400">
                        Venta: {{ formatCurrency(detalle.precio_unitario) }}
                      </div>
                    </td>

                    <!-- Total -->
                    <td v-if="index === 0" class="px-6 py-4 whitespace-nowrap" :rowspan="venta.detalles.length">
                      <div class="text-sm font-medium text-white">
                        {{ formatCurrency(venta.total) }}
                      </div>
                    </td>



                    <!-- Acciones (solo en la primera fila de cada venta) -->
                    <td v-if="index === 0" class="px-6 py-4 whitespace-nowrap text-sm font-medium" :rowspan="venta.detalles.length">
                      <div class="flex space-x-2">
                        <button
                          @click="imprimirRecibo(venta)"
                          class="text-green-400 hover:text-green-300 transition-colors duration-200"
                          title="Imprimir recibo"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                          </svg>
                        </button>
                        <button
                          @click="router.visit(route('ventas.edit', venta.id_venta))"
                          class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                          title="Editar"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                          </svg>
                        </button>
                        <button
                          @click="viewVenta(venta)"
                          class="text-blue-400 hover:text-blue-300 transition-colors duration-200"
                          title="Ver Venta"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                          </svg>
                        </button>
                        <button
                          @click="deleteVenta(venta.id_venta)"
                          class="text-red-400 hover:text-red-300 transition-colors duration-200"
                          title="Anular Venta"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>

                <!-- No data message -->
                <tr v-if="ventas.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center">
                    <div class="text-blue-300 text-lg">No hay ventas registradas</div>
                    <div class="text-gray-400 text-sm mt-2">Crea una nueva venta para comenzar</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>

    <!-- Visor de PDF Modal -->
    <div v-if="mostrarPdf" class="fixed inset-0 z-50 overflow-hidden">
      <!-- Overlay de fondo -->
      <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>

      <!-- Modal del PDF -->
      <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-5xl h-[95vh] bg-white rounded-xl shadow-2xl overflow-hidden">
          <!-- Header del modal con controles profesionales -->
          <div class="bg-white border-b">
            <!-- Barra superior con botones principales -->
            <div class="flex items-center justify-between p-3 bg-gray-100">
              <div class="flex items-center space-x-3">
                <button
                  @click="abrirNuevaPestana"
                  class="flex items-center space-x-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors"
                  title="Abrir nueva pestaña"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                  </svg>
                  <span class="text-sm font-medium">Abrir nueva pestaña</span>
                </button>
                <button
                  @click="descargarPdf"
                  class="flex items-center space-x-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors"
                  title="Guardar en PDF"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <span class="text-sm font-medium">Descargar PDF</span>
                </button>
                <button
                  @click="imprimirPdf"
                  class="flex items-center space-x-2 px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition-colors"
                  title="Imprimir"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                  </svg>
                  <span class="text-sm font-medium">Imprimir</span>
                </button>
              </div>

              <!-- Botón de cerrar -->
              <button
                @click="cerrarPdf"
                class="flex items-center justify-center w-10 h-10 text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                title="Cerrar"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Contenido del PDF -->
          <div class="flex-1 h-full">
            <iframe
              v-if="pdfUrl"
              :src="pdfUrl"
              class="w-full h-full border-0"
              title="Recibo de Venta"
            ></iframe>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'

interface Cliente {
  nombre: string
}

interface Modelo {
  id_modelo: number
  nombre: string
}

interface Producto {
  id_producto: number
  descripcion?: string
  modelo?: Modelo
  categoria?: { nombre: string }
  marca?: { nombre: string }
}

interface DetalleVenta {
  id_detalle_venta: number
  cantidad: number
  precio_unitario: number
  descripcion: string
  precio_compra: number
  ganancia_por_unidad: number
  ganancia_total: number
  producto?: Producto
}

interface Venta {
  id_venta: number
  total: number
  ganancia_total: number
  cliente?: Cliente
  detalles?: DetalleVenta[]
}

const props = defineProps<{
  ventas?: Venta[]
  total_ventas?: number
  total_ganancia?: number
  cantidad_ventas?: number
  search?: string
}>()


// Variables para búsqueda
const busqueda = ref('') // Siempre empezar sin búsqueda
const searchTimeout = ref<NodeJS.Timeout | null>(null)

// Variables para el modal del PDF
const mostrarPdf = ref(false)
const pdfUrl = ref('')
const pdfData = ref(null)

// Valores por defecto para evitar errores
const totalVentas = computed(() => props.total_ventas || 0)
const totalGanancia = computed(() => props.total_ganancia || 0)
const cantidadVentas = computed(() => props.cantidad_ventas || 0)
const ventas = computed(() => props.ventas || [])


const imprimirRecibo = async (venta: Venta) => {
  try {
    const response = await fetch(route('ventas.recibo', venta.id_venta), {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      }
    });

    const result = await response.json();

    if (result.success) {
      // Convertir base64 a blob y crear URL
      const pdfBlob = new Blob([Uint8Array.from(atob(result.pdf), c => c.charCodeAt(0))], { type: 'application/pdf' });
      const url = URL.createObjectURL(pdfBlob);

      // Guardar datos del PDF
      pdfData.value = result;
      pdfUrl.value = url;

      // Mostrar modal
      mostrarPdf.value = true;
    } else {
      alert('Error al generar el recibo: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al generar el recibo');
  }
}

const formatCurrency = (amount: number) => {
  if (isNaN(amount) || amount === null || amount === undefined) {
    return 'Bs 0.00'
  }
  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount).replace('BOB', 'Bs')
}

// Métodos para manejar el modal del PDF
const cerrarPdf = () => {
  mostrarPdf.value = false;
  if (pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = '';
  }
  pdfData.value = null;
}

const descargarPdf = () => {
  if (pdfData.value) {
    const link = document.createElement('a');
    link.href = pdfUrl.value;
    link.download = pdfData.value.filename || 'recibo_venta.pdf';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}

const abrirNuevaPestana = () => {
  if (pdfUrl.value) {
    window.open(pdfUrl.value, '_blank');
  }
}

const imprimirPdf = () => {
  if (pdfUrl.value) {
    const printWindow = window.open(pdfUrl.value);
    printWindow.onload = () => {
      printWindow.print();
    };
  }
}

const viewVenta = (venta: Venta) => {
  router.visit(route('ventas.show', venta.id_venta))
}

const deleteVenta = (id: number) => {
  Swal.fire({
    title: '¿Anular venta?',
    text: 'Esta acción anulará la venta y devolverá el stock de los productos correspondientes. Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, anular',
    cancelButtonText: 'Cancelar',
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('ventas.destroy', id), {
        onSuccess: () => {
          Swal.fire({
            title: '¡Venta anulada!',
            text: 'La venta ha sido anulada y el stock de los productos ha sido devuelto.',
            icon: 'success',
            confirmButtonText: 'Entendido'
          });
        },
        onError: () => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo anular la venta. Inténtalo de nuevo.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          });
        }
      });
    }
  });
}


// Funciones de búsqueda
const debounceSearch = () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(() => {
    performSearch()
  }, 300) // Debounce de 300ms
}

const performSearch = () => {
  if (busqueda.value.trim()) {
    // Buscar con el término
    router.get(route('ventas.index'), {
      search: busqueda.value.trim()
    }, {
      preserveState: true,
      preserveScroll: true,
      replace: true
    })
  } else {
    // Si no hay búsqueda, mostrar todas las ventas
    router.get(route('ventas.index'), {}, {
      preserveState: true,
      preserveScroll: true,
      replace: true
    })
  }
}

const clearSearch = () => {
  busqueda.value = ''
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  // Volver a cargar sin filtro de búsqueda (mostrar todas las ventas)
  router.get(route('ventas.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}


onMounted(() => {
  // El componente ya recibe las ventas desde el backend
})
</script>
