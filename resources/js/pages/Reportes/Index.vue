<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Sistema de Reportes</h1>
            <p class="text-purple-300">Genera reportes detallados de tu inventario y operaciones</p>
          </div>
        </div>

        <!-- Reportes Disponibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Reporte de Productos -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:border-purple-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-purple-600/20 rounded-lg">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Productos</h3>
            </div>
            <p class="text-purple-300 mb-4">Genera reportes detallados de productos con filtros por stock, categoría, marca y más.</p>
            <button
              @click="router.visit(route('reportes.productos'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>

          <!-- Reporte de Ventas -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:border-purple-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-green-600/20 rounded-lg">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Ventas</h3>
            </div>
            <p class="text-purple-300 mb-4">Analiza el rendimiento de ventas por período, cliente y producto.</p>
            <button
              class="w-full px-4 py-2 bg-gray-600 text-gray-300 font-semibold rounded-lg cursor-not-allowed"
              disabled
            >
              Próximamente
            </button>
          </div>

          <!-- Reporte de Compras -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:border-purple-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-blue-600/20 rounded-lg">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Compras</h3>
            </div>
            <p class="text-purple-300 mb-4">Revisa el historial de compras y proveedores por período.</p>
            <button
              class="w-full px-4 py-2 bg-gray-600 text-gray-300 font-semibold rounded-lg cursor-not-allowed"
              disabled
            >
              Próximamente
            </button>
          </div>

          <!-- Reporte de Stock -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:border-purple-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-yellow-600/20 rounded-lg">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Stock</h3>
            </div>
            <p class="text-purple-300 mb-4">Monitorea niveles de inventario y alertas de stock bajo.</p>
            <button
              class="w-full px-4 py-2 bg-gray-600 text-gray-300 font-semibold rounded-lg cursor-not-allowed"
              disabled
            >
              Próximamente
            </button>
          </div>

          <!-- Reporte de Clientes -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:border-purple-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-indigo-600/20 rounded-lg">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Clientes</h3>
            </div>
            <p class="text-purple-300 mb-4">Analiza el comportamiento y preferencias de tus clientes.</p>
            <button
              class="w-full px-4 py-2 bg-gray-600 text-gray-300 font-semibold rounded-lg cursor-not-allowed"
              disabled
            >
              Próximamente
            </button>
          </div>

          <!-- Reporte de Proveedores -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 p-6 hover:border-purple-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-red-600/20 rounded-lg">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Proveedores</h3>
            </div>
            <p class="text-purple-300 mb-4">Evalúa el rendimiento y confiabilidad de tus proveedores.</p>
            <button
              @click="router.visit(route('reportes.proveedores'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
</script>
