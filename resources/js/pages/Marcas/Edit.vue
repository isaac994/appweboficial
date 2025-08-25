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
                Editar Marca
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Modifica la información de la marca</p>
          </div>
          <Link
            :href="route('marcas.index')"
            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
          </Link>
        </div>

        <!-- Formulario -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 gap-6">
              <!-- Nombre de la Marca -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Nombre de la Marca *
                </label>
                <input
                  v-model="form.nombre"
                  type="text"
                  required
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.nombre }"
                  placeholder="Nombre de la marca"
                />
                <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-400">{{ form.errors.nombre }}</p>
              </div>

              <!-- País de Origen -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  País de Origen
                </label>
                <input
                  v-model="form.pais_origen"
                  type="text"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.pais_origen }"
                  placeholder="País de origen de la marca"
                />
                <p v-if="form.errors.pais_origen" class="mt-1 text-sm text-red-400">{{ form.errors.pais_origen }}</p>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-purple-500/30">
              <Link
                :href="route('marcas.index')"
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
                  Actualizando...
                </span>
                <span v-else>Actualizar Marca</span>
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
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Marca {
  id_marca: number
  nombre: string
  pais_origen: string | null
}

interface Props {
  marca: Marca
  errors: Record<string, string>
}

const props = defineProps<Props>()

const form = useForm({
  nombre: props.marca.nombre,
  pais_origen: props.marca.pais_origen || ''
})

const isSubmitting = ref(false)

const submitForm = () => {
  isSubmitting.value = true

  form.put(route('marcas.update', props.marca.id_marca), {
    onSuccess: () => {
      isSubmitting.value = false
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}
</script>
