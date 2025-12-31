<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">
              <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                Reporte General de Negocio
              </span>
            </h1>
            <p class="text-blue-300">Análisis completo de compras, ventas, ganancias y productos más vendidos</p>
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

        <!-- Filtros Simples -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 mb-8">
          <div class="flex flex-col md:flex-row gap-4 items-end">
            <!-- Fecha Inicio -->
            <div class="flex-1">
              <label class="block text-sm font-medium text-blue-300 mb-2">Desde</label>
              <input
                v-model="filtros.fecha_inicio"
                type="date"
                class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @change="generarReporte"
              />
            </div>

            <!-- Fecha Fin -->
            <div class="flex-1">
              <label class="block text-sm font-medium text-blue-300 mb-2">Hasta</label>
              <input
                v-model="filtros.fecha_fin"
                type="date"
                class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @change="generarReporte"
              />
            </div>

            <!-- Botón Exportar PDF -->
            <button
              @click="exportarPDF"
              :disabled="cargando || !datos"
              class="px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 disabled:from-gray-600 disabled:to-gray-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center"
            >
              <svg v-if="cargando" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              {{ cargando ? 'Generando...' : 'Descargar PDF' }}
            </button>
          </div>
        </div>

        <!-- Sección Reporte de Compras -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-white mb-6">
            <span class="bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
              📦 Reporte de Compras
            </span>
          </h2>

          <!-- Filtros de Compras -->
          <div class="bg-black/10 backdrop-blur-xl rounded-xl border border-green-500/30 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Filtros de Fecha -->
              <div class="lg:col-span-1">
                <h3 class="text-lg font-semibold text-white mb-4">📅 Período</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Periodo Inicial</label>
                    <input
                      v-model="filtrosCompras.periodo_inicial"
                      type="text"
                      placeholder="10-2025"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Periodo Final</label>
                    <input
                      v-model="filtrosCompras.periodo_final"
                      type="text"
                      placeholder="10-2025"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>

              <!-- Filtros de Opciones -->
              <div class="lg:col-span-1">
                <h3 class="text-lg font-semibold text-white mb-4">⚙️ Opciones</h3>
                <div class="space-y-3">
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.fecha_ocurrencia"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Fecha de Ocurrencia</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.sin_remesas"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Sin Remesas</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.solamente_cc"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Solamente C/C</span>
                  </label>
                </div>
              </div>

              <!-- Filtros de Búsqueda -->
              <div class="lg:col-span-1">
                <h3 class="text-lg font-semibold text-white mb-4">🔍 Búsqueda</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Solo Valor</label>
                    <input
                      v-model="filtrosCompras.solo_valor"
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Tipo</label>
                    <select
                      v-model="filtrosCompras.tipo"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                      <option value="">Seleccionar</option>
                      <option value="compra">Compra</option>
                      <option value="gasto">Gasto</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Estado</label>
                    <select
                      v-model="filtrosCompras.estado"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                      <option value="todas">Todas</option>
                      <option value="activo">Activo</option>
                      <option value="inactivo">Inactivo</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selección de Marcas y Categorías -->
            <div class="mt-6">
              <h3 class="text-lg font-semibold text-white mb-4">🏷️ Marcas y Categorías</h3>

              <!-- Marcas -->
              <div class="mb-6">
                <h4 class="text-md font-medium text-green-300 mb-3">Marcas</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                  <label
                    v-for="marca in marcas"
                    :key="marca.id"
                    class="flex items-center p-2 bg-black/20 rounded-lg border border-green-500/30 hover:bg-green-500/10 transition-colors cursor-pointer"
                  >
                    <input
                      v-model="filtrosCompras.marcas_seleccionadas"
                      :value="marca.id"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-white text-sm">{{ marca.nombre }}</span>
                  </label>
                </div>
              </div>

              <!-- Categorías -->
              <div class="mb-6">
                <h4 class="text-md font-medium text-green-300 mb-3">Categorías</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                  <label
                    v-for="categoria in categorias"
                    :key="categoria.id"
                    class="flex items-center p-2 bg-black/20 rounded-lg border border-green-500/30 hover:bg-green-500/10 transition-colors cursor-pointer"
                  >
                    <input
                      v-model="filtrosCompras.categorias_seleccionadas"
                      :value="categoria.id"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-white text-sm">{{ categoria.nombre }}</span>
                  </label>
                </div>
              </div>

              <!-- Botones de Acción -->
              <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex items-center">
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.exportar_excel"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Exportar datos al Excel</span>
                  </label>
                </div>

                <button
                  @click="generarReporteCompras"
                  :disabled="cargandoCompras"
                  class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-300 flex items-center"
                >
                  <svg v-if="cargandoCompras" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  {{ cargandoCompras ? 'Generando...' : 'Listar' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Sección Reporte de Compras -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-white mb-6">
            <span class="bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
              📦 Reporte de Compras
            </span>
          </h2>

          <!-- Filtros de Compras -->
          <div class="bg-black/10 backdrop-blur-xl rounded-xl border border-green-500/30 p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Filtros de Fecha -->
              <div class="lg:col-span-1">
                <h3 class="text-lg font-semibold text-white mb-4">📅 Período</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Periodo Inicial</label>
                    <input
                      v-model="filtrosCompras.periodo_inicial"
                      type="text"
                      placeholder="10-2025"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Periodo Final</label>
                    <input
                      v-model="filtrosCompras.periodo_final"
                      type="text"
                      placeholder="10-2025"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                  </div>
                </div>
              </div>

              <!-- Filtros de Opciones -->
              <div class="lg:col-span-1">
                <h3 class="text-lg font-semibold text-white mb-4">⚙️ Opciones</h3>
                <div class="space-y-3">
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.fecha_ocurrencia"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Fecha de Ocurrencia</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.sin_remesas"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Sin Remesas</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.solamente_cc"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Solamente C/C</span>
                  </label>
                </div>
              </div>

              <!-- Filtros de Búsqueda -->
              <div class="lg:col-span-1">
                <h3 class="text-lg font-semibold text-white mb-4">🔍 Búsqueda</h3>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Solo Valor</label>
                    <input
                      v-model="filtrosCompras.solo_valor"
                      type="number"
                      step="0.01"
                      placeholder="0.00"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Tipo</label>
                    <select
                      v-model="filtrosCompras.tipo"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                      <option value="">Seleccionar</option>
                      <option value="compra">Compra</option>
                      <option value="gasto">Gasto</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-green-300 mb-2">Estado</label>
                    <select
                      v-model="filtrosCompras.estado"
                      class="w-full px-3 py-2 bg-black/30 border border-green-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                      <option value="todas">Todas</option>
                      <option value="activo">Activo</option>
                      <option value="inactivo">Inactivo</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Selección de Marcas y Categorías -->
            <div class="mt-6">
              <h3 class="text-lg font-semibold text-white mb-4">🏷️ Marcas y Categorías</h3>

              <!-- Marcas -->
              <div class="mb-6">
                <h4 class="text-md font-medium text-green-300 mb-3">Marcas</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                  <label
                    v-for="marca in marcas"
                    :key="marca.id"
                    class="flex items-center p-2 bg-black/20 rounded-lg border border-green-500/30 hover:bg-green-500/10 transition-colors cursor-pointer"
                  >
                    <input
                      v-model="filtrosCompras.marcas_seleccionadas"
                      :value="marca.id"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-white text-sm">{{ marca.nombre }}</span>
                  </label>
                </div>
              </div>

              <!-- Categorías -->
              <div class="mb-6">
                <h4 class="text-md font-medium text-green-300 mb-3">Categorías</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                  <label
                    v-for="categoria in categorias"
                    :key="categoria.id"
                    class="flex items-center p-2 bg-black/20 rounded-lg border border-green-500/30 hover:bg-green-500/10 transition-colors cursor-pointer"
                  >
                    <input
                      v-model="filtrosCompras.categorias_seleccionadas"
                      :value="categoria.id"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-white text-sm">{{ categoria.nombre }}</span>
                  </label>
                </div>
              </div>

              <!-- Botones de Acción -->
              <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                <div class="flex items-center">
                  <label class="flex items-center">
                    <input
                      v-model="filtrosCompras.exportar_excel"
                      type="checkbox"
                      class="w-4 h-4 text-green-500 bg-black/30 border-green-500/50 rounded focus:ring-green-500 focus:ring-2"
                    />
                    <span class="ml-2 text-green-300">Exportar datos al Excel</span>
                  </label>
                </div>

                <button
                  @click="generarReporteCompras"
                  :disabled="cargandoCompras"
                  class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-300 flex items-center"
                >
                  <svg v-if="cargandoCompras" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  {{ cargandoCompras ? 'Generando...' : 'Listar' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumen Principal -->
        <div v-if="datos" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Compras -->
          <div class="bg-gradient-to-r from-green-600/20 to-emerald-600/20 border border-green-500/30 rounded-lg p-6">
            <div class="text-sm text-green-300 mb-2">💰 Total Compras</div>
            <div class="text-3xl font-bold text-white mb-2">{{ formatCurrency(datos.totales.total_compras) }}</div>
            <div class="text-sm text-green-400">{{ datos.totales.numero_compras }} compras realizadas</div>
          </div>

          <!-- Total Ventas -->
          <div class="bg-gradient-to-r from-blue-600/20 to-cyan-600/20 border border-blue-500/30 rounded-lg p-6">
            <div class="text-sm text-blue-300 mb-2">💵 Total Ventas</div>
            <div class="text-3xl font-bold text-white mb-2">{{ formatCurrency(datos.totales.total_ventas) }}</div>
            <div class="text-sm text-blue-400">{{ datos.totales.numero_ventas }} ventas realizadas</div>
          </div>

          <!-- Ganancias -->
          <div class="bg-gradient-to-r from-purple-600/20 to-pink-600/20 border border-purple-500/30 rounded-lg p-6">
            <div class="text-sm text-purple-300 mb-2">📈 Ganancias</div>
            <div class="text-3xl font-bold text-white mb-2">{{ formatCurrency(datos.totales.ganancias) }}</div>
            <div class="text-sm text-purple-400">{{ datos.totales.margen_ganancia }}% de margen</div>
          </div>

          <!-- Productos Vendidos -->
          <div class="bg-gradient-to-r from-yellow-600/20 to-orange-600/20 border border-yellow-500/30 rounded-lg p-6">
            <div class="text-sm text-yellow-300 mb-2">📦 Productos Vendidos</div>
            <div class="text-3xl font-bold text-white mb-2">{{ datos.totales.total_productos_vendidos }}</div>
            <div class="text-sm text-yellow-400">unidades vendidas</div>
          </div>
        </div>

        <!-- Información Adicional -->
        <div v-if="datos" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Promedios -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">📊 Promedios por Transacción</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-blue-300">Promedio Compra:</span>
                <span class="text-white font-semibold">{{ formatCurrency(datos.totales.promedio_compra) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-blue-300">Promedio Venta:</span>
                <span class="text-white font-semibold">{{ formatCurrency(datos.totales.promedio_venta) }}</span>
              </div>
            </div>
          </div>

          <!-- Eficiencia -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">⚡ Eficiencia del Negocio</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-blue-300">Rotación Inventario:</span>
                <span class="text-white font-semibold">{{ datos.totales.rotacion_inventario }}x</span>
              </div>
              <div class="flex justify-between">
                <span class="text-blue-300">Días de Inventario:</span>
                <span class="text-white font-semibold">{{ datos.totales.dias_inventario }} días</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Productos con Stock Bajo -->
        <div v-if="datos && datos.productos_stock_bajo && datos.productos_stock_bajo.length > 0" class="bg-red-500/20 backdrop-blur-xl rounded-xl border border-red-500/30 p-6 mb-8">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-red-400">Productos con Stock Bajo</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="producto in datos.productos_stock_bajo"
                    :key="producto.id_producto"
                    class="bg-red-600/10 border border-red-500/30 rounded-lg p-4"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-white font-medium">{{ producto.nombre }}</h4>
                            <p class="text-red-300 text-sm">{{ producto.marca }} - {{ producto.categoria }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-red-400">{{ producto.stock_actual }}</span>
                            <p class="text-red-300 text-xs">unidades</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos Más Vendidos -->
        <div v-if="datos && datos.productos_mas_vendidos.length > 0" class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 mb-8">
          <h3 class="text-xl font-semibold text-white mb-4">🏆 Top 5 Productos Más Vendidos</h3>
          <div class="space-y-3">
            <div
              v-for="(producto, index) in datos.productos_mas_vendidos.slice(0, 5)"
              :key="producto.id_producto"
              class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-blue-500/20"
            >
              <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center mr-3">
                  <span class="text-blue-400 font-bold text-sm">{{ index + 1 }}</span>
                </div>
                <div>
                  <div class="text-white font-medium">{{ producto.nombre }}</div>
                  <div class="text-xs text-gray-400">{{ producto.categoria }} - {{ producto.marca }}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="text-white font-semibold">{{ producto.cantidad_vendida }} unidades</div>
                <div class="text-sm text-blue-300">{{ formatCurrency(producto.ingresos) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumen del Período -->
        <div v-if="datos && datos.analisis_periodo.length > 0" class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6">
          <h3 class="text-xl font-semibold text-white mb-4">📅 Resumen del Período Seleccionado</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="periodo in datos.analisis_periodo.slice(0, 6)"
              :key="periodo.periodo"
              class="bg-black/30 rounded-lg p-4 border border-blue-500/20"
            >
              <div class="text-sm text-blue-300 mb-2">{{ periodo.periodo }}</div>
              <div class="space-y-1">
                <div class="flex justify-between text-xs">
                  <span class="text-gray-400">Compras:</span>
                  <span class="text-white">{{ formatCurrency(periodo.compras) }}</span>
                </div>
                <div class="flex justify-between text-xs">
                  <span class="text-gray-400">Ventas:</span>
                  <span class="text-white">{{ formatCurrency(periodo.ventas) }}</span>
                </div>
                <div class="flex justify-between text-xs">
                  <span class="text-gray-400">Ganancias:</span>
                  <span :class="periodo.ganancias >= 0 ? 'text-green-400' : 'text-red-400'" class="font-semibold">
                    {{ formatCurrency(periodo.ganancias) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado de Carga -->
        <div v-if="!datos && !cargando" class="text-center py-12">
          <svg class="w-16 h-16 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
          <h3 class="text-lg font-medium text-white mb-2">Selecciona un período para generar el reporte</h3>
          <p class="text-blue-300">Usa los filtros de fecha para analizar el rendimiento del negocio</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const datos = ref(null);
const cargando = ref(false);
const cargandoCompras = ref(false);

const filtros = ref({
  fecha_inicio: '',
  fecha_fin: ''
});

const filtrosCompras = ref({
  periodo_inicial: '10-2025',
  periodo_final: '10-2025',
  fecha_ocurrencia: false,
  sin_remesas: false,
  solamente_cc: false,
  solo_valor: '',
  tipo: '',
  estado: 'todas',
  marcas_seleccionadas: [],
  categorias_seleccionadas: [],
  exportar_excel: false
});

// Datos de ejemplo para marcas y categorías
const marcas = ref([
  { id: 1, nombre: 'Samsung' },
  { id: 2, nombre: 'Apple' },
  { id: 3, nombre: 'Sony' },
  { id: 4, nombre: 'LG' },
  { id: 5, nombre: 'HP' },
  { id: 6, nombre: 'Dell' },
  { id: 7, nombre: 'Canon' },
  { id: 8, nombre: 'Nikon' },
  { id: 9, nombre: 'Xiaomi' },
  { id: 10, nombre: 'Huawei' }
]);

const categorias = ref([
  { id: 1, nombre: 'Electrónicos' },
  { id: 2, nombre: 'Computadoras' },
  { id: 3, nombre: 'Smartphones' },
  { id: 4, nombre: 'Accesorios' },
  { id: 5, nombre: 'Audio' },
  { id: 6, nombre: 'Video' },
  { id: 7, nombre: 'Gaming' },
  { id: 8, nombre: 'Oficina' },
  { id: 9, nombre: 'Hogar' },
  { id: 10, nombre: 'Deportes' }
]);

const generarReporte = async () => {
  if (!filtros.value.fecha_inicio || !filtros.value.fecha_fin) {
    return;
  }

  cargando.value = true;

  try {
    const response = await fetch(route('reportes.general.generar'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filtros.value)
    });

    const result = await response.json();

    if (result.success) {
      datos.value = result.data;
    } else {
      console.error('Error al generar reporte:', result.message);
    }
  } catch (error) {
    console.error('Error al generar reporte:', error);
  } finally {
    cargando.value = false;
  }
};

const exportarPDF = async () => {
  if (!filtros.value.fecha_inicio || !filtros.value.fecha_fin) {
    alert('Por favor selecciona un período de fechas');
    return;
  }

  cargando.value = true;

  try {
    const response = await fetch(route('reportes.general.pdf'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
      },
      body: JSON.stringify(filtros.value)
    });

    const result = await response.json();

    if (result.success) {
      // Crear enlace de descarga
      const link = document.createElement('a');
      link.href = 'data:application/pdf;base64,' + result.pdf;
      link.download = result.filename;
      link.click();
    } else {
      console.error('Error al exportar PDF:', result.message);
    }
  } catch (error) {
    console.error('Error al exportar PDF:', error);
  } finally {
    cargando.value = false;
  }
};

const generarReporteCompras = async () => {
  cargandoCompras.value = true;

  try {
    // Simular llamada a API
    await new Promise(resolve => setTimeout(resolve, 2000));

    // Aquí iría la lógica real para generar el reporte de compras
    console.log('Generando reporte de compras con filtros:', filtrosCompras.value);

    // Mostrar mensaje de éxito
    alert('Reporte de compras generado exitosamente');

  } catch (error) {
    console.error('Error al generar reporte de compras:', error);
    alert('Error al generar el reporte de compras');
  } finally {
    cargandoCompras.value = false;
  }
};

const formatCurrency = (value: number) => {
  if (value === null || value === undefined || isNaN(value)) {
    return 'Bs 0.00';
  }

  return new Intl.NumberFormat('es-BO', {
    style: 'currency',
    currency: 'BOB',
    minimumFractionDigits: 2,
  }).format(value).replace('BOB', 'Bs');
};

onMounted(() => {
  // Establecer fechas por defecto (último mes)
  const hoy = new Date();
  const haceUnMes = new Date();
  haceUnMes.setMonth(hoy.getMonth() - 1);

  filtros.value.fecha_inicio = haceUnMes.toISOString().split('T')[0];
  filtros.value.fecha_fin = hoy.toISOString().split('T')[0];
});
</script>
