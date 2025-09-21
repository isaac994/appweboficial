<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Reporte de Proveedores</h1>
            <p class="text-purple-300">Genera reportes detallados de proveedores con filtros avanzados</p>
          </div>
          <button
            @click="router.visit(route('reportes.index'))"
            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-300"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
          </button>
        </div>

        <!-- Filtros y Configuración -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <!-- Primera fila: Ordenamiento y filtros básicos -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Ordenar por -->
           

            <!-- Búsqueda -->


            <!-- Orden -->
            <div>
              <label class="block text-sm font-medium text-purple-300 mb-2">Orden</label>
              <select
                v-model="filtros.orden"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="asc">Ascendente (A-Z)</option>
                <option value="desc">Descendente (Z-A)</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Botón generar reporte -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 mb-8">
          <div class="flex justify-center">
            <button
              @click="generarReporte"
              class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-300 flex items-center"
              :disabled="generandoReporte"
            >
              <svg v-if="!generandoReporte" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              <svg v-else class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ generandoReporte ? 'Generando...' : 'Generar Reporte' }}
            </button>
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
                      <span class="text-sm font-medium">Guardar en PDF</span>
                    </button>
                  </div>
                  <button
                    @click="cerrarPdf"
                    class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-200 rounded-lg transition-colors"
                    title="Cerrar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>

                <!-- Barra de herramientas del PDF -->
                <div class="flex items-center justify-between p-3 bg-gray-800 text-white">
                  <div class="flex items-center space-x-4">
                    <!-- Menú hamburguesa -->
                    <button class="p-2 hover:bg-gray-700 rounded">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                      </svg>
                    </button>

                    <!-- Información del documento -->
                    <div class="flex items-center space-x-3">
                      <span class="text-sm text-gray-300">Documento: {{ pdfData?.filename || 'reporte.pdf' }}</span>
                      <div class="w-px h-4 bg-gray-600"></div>
                      <span class="text-sm">Página 1 / 1</span>
                    </div>
                  </div>

                  <div class="flex items-center space-x-4">
                    <!-- Controles de zoom -->
                    <div class="flex items-center space-x-2">
                      <button class="p-1 hover:bg-gray-700 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                      </button>
                      <span class="text-sm px-2 py-1 bg-gray-700 rounded">100%</span>
                      <button class="p-1 hover:bg-gray-700 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                      </button>
                    </div>

                    <div class="w-px h-4 bg-gray-600"></div>

                    <!-- Herramientas adicionales -->
                    <button class="p-2 hover:bg-gray-700 rounded" title="Ajustar a página">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                      </svg>
                    </button>

                    <button class="p-2 hover:bg-gray-700 rounded" title="Rotar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                      </svg>
                    </button>

                    <div class="w-px h-4 bg-gray-600"></div>

                    <!-- Botones de acción -->
                    <button @click="descargarPdf" class="p-2 hover:bg-gray-700 rounded" title="Descargar">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </button>

                    <button @click="imprimirPdf" class="p-2 hover:bg-gray-700 rounded" title="Imprimir">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                      </svg>
                    </button>

                    <button class="p-2 hover:bg-gray-700 rounded" title="Más opciones">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Contenido del PDF sin miniaturas -->
              <div class="flex-1 h-full">
                <iframe
                  v-if="pdfUrl"
                  :src="pdfUrl"
                  class="w-full h-full border-0"
                  title="Reporte de Proveedores"
                ></iframe>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const page = usePage();

// Props del componente
const props = defineProps<{
  proveedores: any;
  filtros: any;
}>();

// Estado reactivo
const generandoReporte = ref(false);
const mostrarPdf = ref(false);
const pdfUrl = ref('');
const pdfData = ref(null);

// Filtros locales
const filtros = reactive({
  search: '',
  ordenar_por: 'nombre',
  orden: 'asc'
});

// Generar reporte
const generarReporte = async () => {
  generandoReporte.value = true;

  try {
    const response = await fetch(route('reportes.generar-proveedores'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filtros)
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
      alert('Error al generar el reporte: ' + result.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error al generar el reporte');
  } finally {
    generandoReporte.value = false;
  }
};

// Métodos para manejar el modal del PDF
const cerrarPdf = () => {
  mostrarPdf.value = false;
  if (pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = '';
  }
  pdfData.value = null;
};

const descargarPdf = () => {
  if (pdfData.value) {
    const link = document.createElement('a');
    link.href = pdfUrl.value;
    link.download = pdfData.value.filename || 'reporte_proveedores.pdf';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
};

const abrirNuevaPestana = () => {
  if (pdfUrl.value) {
    window.open(pdfUrl.value, '_blank');
  }
};

const imprimirPdf = () => {
  if (pdfUrl.value) {
    const printWindow = window.open(pdfUrl.value);
    printWindow.onload = () => {
      printWindow.print();
    };
  }
};

// Limpiar URL del PDF cuando se cierre el modal
watch(mostrarPdf, (newValue) => {
  if (!newValue && pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = '';
    pdfData.value = null;
  }
});

// Lifecycle
onMounted(() => {
  // Los filtros ya están inicializados con valores por defecto
});
</script>
