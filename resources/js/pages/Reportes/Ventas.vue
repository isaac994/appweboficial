<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Reporte de Ventas</h1>
            <p class="text-purple-300">Genera reportes detallados de ventas con filtros por fechas</p>
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


            <!-- Orden -->
            <div>
              <label class="block text-sm font-medium text-purple-300 mb-2">Orden</label>
              <select
                v-model="filtros.orden"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="desc">Descendente</option>
                <option value="asc">Ascendente</option>
              </select>
            </div>

            <!-- Botón Generar -->
            <div class="flex items-end">
              <button
                @click="generarReporte"
                :disabled="generando"
                class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:from-gray-600 disabled:to-gray-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 disabled:hover:scale-100 disabled:cursor-not-allowed"
              >
                <span v-if="generando" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Generando...
                </span>
                <span v-else class="flex items-center justify-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Generar Reporte
                </span>
              </button>
            </div>
          </div>

          <!-- Segunda fila: Filtros de fecha -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Fecha Inicio -->
            <div>
              <label class="block text-sm font-medium text-purple-300 mb-2">Fecha de Inicio</label>
              <input
                v-model="filtros.fecha_inicio"
                type="date"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                placeholder="Seleccionar fecha de inicio"
              />
              <p class="mt-1 text-xs text-purple-400">Dejar vacío para incluir desde el inicio</p>
            </div>

            <!-- Fecha Fin -->
            <div>
              <label class="block text-sm font-medium text-purple-300 mb-2">Fecha de Fin</label>
              <input
                v-model="filtros.fecha_fin"
                type="date"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                placeholder="Seleccionar fecha de fin"
              />
              <p class="mt-1 text-xs text-purple-400">Dejar vacío para incluir hasta hoy</p>
            </div>
          </div>
        </div>

        <!-- Información del Reporte -->
        
      </div>

      <!-- Modal de PDF -->
      <div v-if="mostrarPdf" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full h-full max-h-[90vh] flex flex-col">
          <!-- Header del Modal -->
          <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Vista Previa del Reporte de Ventas</h3>
            <div class="flex space-x-2">
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
                class="flex items-center space-x-2 px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-lg transition-colors"
                title="Descargar PDF"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-medium">Descargar</span>
              </button>
              <button
                @click="imprimirPdf"
                class="flex items-center space-x-2 px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white rounded-lg transition-colors"
                title="Imprimir PDF"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span class="text-sm font-medium">Imprimir</span>
              </button>
              <button
                @click="cerrarPdf"
                class="flex items-center space-x-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                title="Cerrar vista previa"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <span class="text-sm font-medium">Cerrar</span>
              </button>
            </div>
          </div>

          <!-- Contenido del PDF -->
          <div class="flex-1 h-full">
            <iframe
              v-if="pdfUrl"
              :src="pdfUrl"
              class="w-full h-full border-0"
              title="Reporte de Ventas"
            ></iframe>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

// Variables reactivas
const generando = ref(false)
const mostrarPdf = ref(false)
const pdfUrl = ref('')
const pdfData = ref(null)

// Filtros
const filtros = ref({
  fecha_inicio: '',
  fecha_fin: '',
  ordenar_por: 'fecha',
  orden: 'desc'
})

// Funciones
const generarReporte = async () => {
  generando.value = true

  try {
    const response = await fetch(route('reportes.generar-ventas'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filtros.value)
    })

    const data = await response.json()

    if (data.success) {
      // Convertir base64 a blob y crear URL
      const pdfBlob = new Blob([Uint8Array.from(atob(data.pdf), c => c.charCodeAt(0))], { type: 'application/pdf' })
      const url = URL.createObjectURL(pdfBlob)

      // Guardar datos del PDF
      pdfData.value = data
      pdfUrl.value = url

      // Mostrar modal
      mostrarPdf.value = true
    } else {
      alert('Error al generar el reporte: ' + data.message)
    }
  } catch (error) {
    console.error('Error:', error)
    alert('Error al generar el reporte')
  } finally {
    generando.value = false
  }
}

const abrirNuevaPestana = () => {
  if (pdfUrl.value) {
    window.open(pdfUrl.value, '_blank')
  }
}

const descargarPdf = () => {
  if (pdfData.value) {
    const link = document.createElement('a')
    link.href = 'data:application/pdf;base64,' + pdfData.value.pdf
    link.download = pdfData.value.filename
    link.click()
  }
}

const imprimirPdf = () => {
  if (pdfUrl.value) {
    const printWindow = window.open(pdfUrl.value)
    printWindow.onload = () => {
      printWindow.print()
    }
  }
}

const cerrarPdf = () => {
  mostrarPdf.value = false
  if (pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value)
    pdfUrl.value = ''
  }
  pdfData.value = null
}

const getOrdenarPorLabel = () => {
  const labels = {
    'fecha': 'Fecha',
    'total': 'Total de Venta',
    'cliente': 'Cliente',
    'usuario': 'Vendedor'
  }
  return labels[filtros.value.ordenar_por] || 'Fecha'
}

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>
