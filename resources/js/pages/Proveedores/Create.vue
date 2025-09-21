<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white">
              <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Crear Nuevo Proveedor
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Agrega un nuevo proveedor al sistema</p>
          </div>
          <Link
            :href="route('proveedores.index')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
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

        <!-- Formulario -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Nombre del Proveedor -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Nombre del Proveedor *
                </label>
                <input
                  v-model="form.nombre"
                  type="text"
                  required
                  maxlength="60"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.nombre }"
                  placeholder="Nombre completo del proveedor (máximo 60 caracteres)"
                />
                <div class="flex justify-between items-center mt-1">
                  <p v-if="form.errors.nombre" class="text-sm text-red-400">{{ form.errors.nombre }}</p>
                  <p class="text-sm text-gray-400">{{ form.nombre.length }}/60 caracteres</p>
                </div>
              </div>

              <!-- CI/NIT -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  CI/NIT *
                </label>
                <input
                  v-model="form.ci_nit"
                  type="text"
                  required
                  maxlength="20"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent font-mono"
                  :class="{ 'border-red-500': form.errors.ci_nit }"
                  placeholder="CI o NIT del proveedor"
                />
                <div class="flex justify-between items-center mt-1">
                  <p v-if="form.errors.ci_nit" class="text-sm text-red-400">{{ form.errors.ci_nit }}</p>
                  <p class="text-sm text-gray-400">{{ form.ci_nit.length }}/20 caracteres</p>
                </div>
              </div>

              <!-- Teléfono -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Teléfono *
                </label>
                <input
                  v-model="form.telefono"
                  type="tel"
                  required
                  maxlength="20"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.telefono }"
                  placeholder="Número de teléfono"
                />
                <div class="flex justify-between items-center mt-1">
                  <p v-if="form.errors.telefono" class="text-sm text-red-400">{{ form.errors.telefono }}</p>
                  <p class="text-sm text-gray-400">{{ form.telefono.length }}/20 caracteres</p>
                </div>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-purple-500/30">
              <Link
                :href="route('proveedores.index')"
                class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isSubmitting" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Guardando...
                </span>
                <span v-else>Guardar Proveedor</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const form = useForm({
  nombre: '',
  ci_nit: '',
  telefono: ''
})

const isSubmitting = ref(false)

const submitForm = () => {
  isSubmitting.value = true

  form.post(route('proveedores.store'), {
    onSuccess: () => {
      isSubmitting.value = false
      Swal.fire({
        title: '¡Éxito!',
        text: 'Proveedor creado exitosamente',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        router.visit(route('proveedores.index'))
      })
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}
</script>
