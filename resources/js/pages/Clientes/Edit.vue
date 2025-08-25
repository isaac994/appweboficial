<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white mb-2">Editar Cliente</h1>
            <p class="text-purple-300">Modifica la información del cliente</p>
          </div>
          <Link
            :href="route('clientes.show', cliente.id_cliente)"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
          </Link>
        </div>

        <!-- Formulario -->
        <div class="bg-black/20 backdrop-blur-xl rounded-xl border border-purple-500/30 overflow-hidden">
          <div class="px-8 py-6 border-b border-purple-500/30">
            <h3 class="text-xl font-semibold text-white">Información del Cliente</h3>
            <p class="text-purple-300 mt-1">Modifica los campos que desees actualizar</p>
          </div>

          <form @submit.prevent="submit" class="p-8 space-y-8">
            <!-- Nombre -->
            <div>
              <label for="nombre" class="block text-sm font-medium text-purple-300 mb-3">
                Nombre Completo *
              </label>
              <input
                id="nombre"
                v-model="form.nombre"
                type="text"
                required
                placeholder="Ingresa el nombre completo del cliente"
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                :class="{ 'border-red-400 focus:ring-red-500': form.errors.nombre }"
              />
              <p v-if="form.errors.nombre" class="mt-2 text-sm text-red-300">
                {{ form.errors.nombre }}
              </p>
            </div>

            <!-- Teléfono -->
            <div>
              <label for="telefono" class="block text-sm font-medium text-purple-300 mb-3">
                Teléfono
              </label>
              <div class="relative">
                <input
                  id="telefono"
                  v-model="form.telefono"
                  type="tel"
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

            <!-- Dirección -->
            <div>
              <label for="direccion" class="block text-sm font-medium text-purple-300 mb-3">
                Dirección
              </label>
              <div class="relative">
                <textarea
                  id="direccion"
                  v-model="form.direccion"
                  rows="3"
                  placeholder="Ingresa la dirección completa del cliente"
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 resize-none"
                  :class="{ 'border-red-400 focus:ring-red-500': form.errors.direccion }"
                ></textarea>
                <div class="absolute top-3 left-4">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
              </div>
              <p v-if="form.errors.direccion" class="mt-2 text-sm text-red-300">
                {{ form.errors.direccion }}
              </p>
            </div>

            <!-- Correo Electrónico -->
            <div>
              <label for="correo_electronico" class="block text-sm font-medium text-purple-300 mb-3">
                Correo Electrónico
              </label>
              <div class="relative">
                <input
                  id="correo_electronico"
                  v-model="form.correo_electronico"
                  type="email"
                  placeholder="Ingresa el correo electrónico"
                  class="w-full px-4 py-3 pl-12 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                  :class="{ 'border-red-400 focus:ring-red-500': form.errors.correo_electronico }"
                />
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center">
                  <svg class="w-5 h-5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
              </div>
              <p v-if="form.errors.correo_electronico" class="mt-2 text-sm text-red-300">
                {{ form.errors.correo_electronico }}
              </p>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-purple-500/30">
              <Link
                :href="route('clientes.show', cliente.id_cliente)"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  cliente: {
    id_cliente: number
    nombre: string
    telefono?: string
    direccion?: string
    correo_electronico?: string
  }
}>()

const form = useForm({
  nombre: props.cliente.nombre,
  telefono: props.cliente.telefono || '',
  direccion: props.cliente.direccion || '',
  correo_electronico: props.cliente.correo_electronico || ''
})

const submit = () => {
  form.put(route('clientes.update', props.cliente.id_cliente))
}
</script>
