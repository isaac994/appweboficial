<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Crear Nuevo Modelo</h1>
            <p class="text-purple-300">Agrega un nuevo modelo de producto</p>
          </div>
          <Link
            :href="route('modelos.index')"
            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200"
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

        <!-- Form -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-blue-500/30 p-8">
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Nombre -->
            <div>
              <label for="nombre" class="block text-sm font-medium text-white mb-2">
                Nombre del Modelo *
              </label>
              <input
                id="nombre"
                v-model="form.nombre"
                type="text"
                required
                class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Ej: iPhone 15 Pro, Galaxy S24, MacBook Air M2"
              />
              <p class="mt-1 text-sm text-purple-300">Nombre descriptivo del modelo</p>
            </div>

            <!-- Descripción -->
            <div>
              <label for="descripcion" class="block text-sm font-medium text-white mb-2">
                Descripción
              </label>
              <textarea
                id="descripcion"
                v-model="form.descripcion"
                rows="4"
                class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Descripción detallada del modelo (opcional)"
              ></textarea>
              <p class="mt-1 text-sm text-purple-300">Información adicional sobre el modelo</p>
            </div>

            <!-- Marca -->
            <div>
              <label for="id_marca" class="block text-sm font-medium text-white mb-2">
                Marca *
              </label>
              <select
                id="id_marca"
                v-model="form.id_marca"
                required
                class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Selecciona una marca</option>
                <option v-for="marca in marcas" :key="marca.id_marca" :value="marca.id_marca">
                  {{ marca.nombre }}
                </option>
              </select>
              <p class="mt-1 text-sm text-purple-300">Selecciona la marca a la que pertenece este modelo</p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-4 pt-6">
              <Link
                :href="route('modelos.index')"
                class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors duration-200"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="loading"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="loading" class="flex items-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Creando...
                </span>
                <span v-else>Crear Modelo</span>
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
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'

// Helper para generar rutas
const route = (name: string, params?: any) => {
  return window.route(name, params)
}

interface Marca {
  id_marca: number
  nombre: string
}

const props = defineProps<{
  marcas: Marca[]
}>()

const loading = ref(false)

const form = ref({
  nombre: '',
  descripcion: '',
  id_marca: ''
})

const submitForm = () => {
  loading.value = true

  router.post(route('modelos.store'), form.value, {
    onSuccess: () => {
      loading.value = false
      Swal.fire({
        title: '¡Éxito!',
        text: 'Modelo creado exitosamente',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        router.visit(route('modelos.index'))
      })
    },
    onError: () => {
      loading.value = false
    }
  })
}
</script>
