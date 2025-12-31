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
                Sistema de Reportes
              </span>
            </h1>
            <p class="text-blue-300">Genera reportes detallados y exporta en PDF para análisis estratégico</p>
          </div>
        </div>

        <!-- Reportes Disponibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Reporte General de Negocio -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gradient-to-r from-blue-600/20 to-cyan-600/20 rounded-lg">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte General</h3>
            </div>
            <p class="text-blue-300 mb-4">Análisis completo de compras, ventas, ganancias y productos más vendidos.</p>
            <button
              @click="router.visit(route('reportes.general'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>

          <!-- Reporte de Compras -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gradient-to-r from-green-600/20 to-emerald-600/20 rounded-lg">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Compras</h3>
            </div>
            <p class="text-blue-300 mb-4">Análisis detallado de compras por período y proveedores.</p>
            <button
              @click="router.visit(route('reportes.compras'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>

          <!-- Reporte de Ventas -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gradient-to-r from-purple-600/20 to-pink-600/20 rounded-lg">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Ventas</h3>
            </div>
            <p class="text-blue-300 mb-4">Análisis de rendimiento de ventas y tendencias por período.</p>
            <button
              @click="router.visit(route('reportes.ventas'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>

          <!-- Reporte de Inventario -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gradient-to-r from-yellow-600/20 to-orange-600/20 rounded-lg">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Inventario</h3>
            </div>
            <p class="text-blue-300 mb-4">Estado actual del inventario, stock y valorización.</p>
            <button
              @click="router.visit(route('reportes.inventario'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>

          <!-- Reporte de Productos -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gradient-to-r from-indigo-600/20 to-blue-600/20 rounded-lg">
                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Productos</h3>
            </div>
            <p class="text-blue-300 mb-4">Análisis de productos más vendidos y rentables.</p>
            <button
              @click="router.visit(route('reportes.productos'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              Generar Reporte
            </button>
          </div>

          <!-- Reporte de Clientes -->
          <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-6 hover:border-blue-400/50 transition-all duration-300">
            <div class="flex items-center mb-4">
              <div class="p-3 bg-gradient-to-r from-teal-600/20 to-cyan-600/20 rounded-lg">
                <svg class="w-6 h-6 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-white ml-3">Reporte de Clientes</h3>
            </div>
            <p class="text-blue-300 mb-4">Análisis de clientes y su comportamiento de compra.</p>
            <button
              @click="router.visit(route('reportes.clientes'))"
              class="w-full px-4 py-2 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white font-semibold rounded-lg transition-all duration-300"
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
