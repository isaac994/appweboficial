<template>
  <AppSidebarLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Crear Nuevo Cliente</h1>
            <p class="text-purple-300">Agrega un nuevo cliente a tu base de datos</p>
          </div>
          <Link
            :href="route('clientes.index')"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
          </Link>
        </div>

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

        <!-- Formulario -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden">
          <div class="px-8 py-6 border-b border-purple-500/30">
            <h3 class="text-xl font-semibold text-white">Información del Cliente</h3>
            <p class="text-purple-300 mt-1">Completa los campos para registrar el nuevo cliente</p>
          </div>

          <form @submit.prevent="submit" class="p-8 space-y-8">
            <!-- Nombre -->
            <div>
              <label for="nombre" class="block text-sm font-medium text-purple-300 mb-3">
                Nombre *
              </label>
              <div class="relative">
                <input
                  id="nombre"
                  v-model="form.nombre"
                  type="text"
                  required
                  maxlength="60"
                  placeholder="Ingresa el nombre completo del cliente (máximo 60 caracteres)"
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-400 focus:ring-red-500': form.errors.nombre }"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
              </div>
              <div class="flex justify-between items-center mt-1">
                <p v-if="form.errors.nombre" class="text-sm text-red-300">{{ form.errors.nombre }}</p>
                <p class="text-sm text-purple-300">{{ form.nombre.length }}/60 caracteres</p>
              </div>
            </div>

            <!-- Apellidos -->
            <div>
              <label for="apellidos" class="block text-sm font-medium text-purple-300 mb-3">
                Apellidos
              </label>
              <div class="relative">
                <input
                  id="apellidos"
                  v-model="form.apellidos"
                  type="text"
                  maxlength="100"
                  placeholder="Ingresa los apellidos del cliente (máximo 100 caracteres)"
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-400 focus:ring-red-500': form.errors.apellidos }"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
              </div>
              <div class="flex justify-between items-center mt-1">
                <p v-if="form.errors.apellidos" class="text-sm text-red-300">{{ form.errors.apellidos }}</p>
                <p class="text-sm text-purple-300">{{ form.apellidos.length }}/100 caracteres</p>
              </div>
            </div>

            <!-- CI -->
            <div>
              <label for="ci" class="block text-sm font-medium text-purple-300 mb-3">
                CI/NIT
              </label>
              <div class="relative">
                <input
                  id="ci"
                  v-model="form.ci"
                  type="text"
                  maxlength="20"
                  placeholder="Ingresa el número de CI o NIT"
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-400 focus:ring-red-500': form.errors.ci }"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                  </svg>
                </div>
              </div>
              <div class="flex justify-between items-center mt-1">
                <p v-if="form.errors.ci" class="text-sm text-red-300">{{ form.errors.ci }}</p>
                <p class="text-sm text-purple-300">{{ form.ci.length }}/20 caracteres</p>
              </div>
            </div>

            <!-- Teléfono -->
            <div>
              <label for="telefono" class="block text-sm font-medium text-purple-300 mb-3">
                Teléfono *
              </label>
              <div class="relative">
                <input
                  id="telefono"
                  v-model="form.telefono"
                  type="tel"
                  required
                  placeholder="Ingresa el número de teléfono"
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-400 focus:ring-red-500': form.errors.telefono }"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                </div>
              </div>
              <p v-if="form.errors.telefono" class="mt-2 text-sm text-red-300">
                {{ form.errors.telefono }}
              </p>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-purple-500/30">
              <Link
                :href="route('clientes.index')"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ isSubmitting ? 'Registrando...' : 'Registrar Cliente' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppSidebarLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import Swal from 'sweetalert2'

const form = useForm({
  nombre: '',
  apellidos: '',
  ci: '',
  telefono: ''
})

const isSubmitting = ref(false)

const submit = () => {
  isSubmitting.value = true

  form.post(route('clientes.store'), {
    onSuccess: () => {
      isSubmitting.value = false
      Swal.fire({
        title: '¡Éxito!',
        text: 'Cliente registrado correctamente',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        router.visit(route('clientes.index'))
      })
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}
</script>
