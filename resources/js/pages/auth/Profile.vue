<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Header -->
    <div class="bg-black/20 backdrop-blur-sm border-b border-purple-500/30">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-white">
              <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                Mi Perfil
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Gestiona tu información personal</p>
          </div>
          <div class="flex items-center space-x-4">
            <Link
              :href="route('dashboard')"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              Dashboard
            </Link>
            <form @submit.prevent="logout" class="inline">
              <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Cerrar Sesión
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido Principal -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Información del Perfil -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <h3 class="text-xl font-semibold text-white mb-6">Información Personal</h3>

          <form @submit.prevent="updateProfile" class="space-y-6">
            <!-- Nombre -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Nombre Completo
              </label>
              <input
                v-model="profileForm.name"
                type="text"
                required
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.name }"
                placeholder="Tu nombre completo"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-400">{{ form.errors.name }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Correo Electrónico
              </label>
              <input
                v-model="profileForm.email"
                type="email"
                required
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.email }"
                placeholder="tu@email.com"
              />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-400">{{ form.errors.email }}</p>
            </div>

            <!-- Botón Actualizar -->
            <div>
              <button
                type="submit"
                :disabled="isUpdating"
                class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isUpdating" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Actualizando...
                </span>
                <span v-else>Actualizar Perfil</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
          <h3 class="text-xl font-semibold text-white mb-6">Cambiar Contraseña</h3>

          <form @submit.prevent="changePassword" class="space-y-6">
            <!-- Contraseña Actual -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Contraseña Actual
              </label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.current_password }"
                placeholder="••••••••"
              />
              <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-400">{{ form.errors.current_password }}</p>
            </div>

            <!-- Nueva Contraseña -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Nueva Contraseña
              </label>
              <input
                v-model="passwordForm.password"
                type="password"
                required
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                :class="{ 'border-red-500': form.errors.password }"
                placeholder="••••••••"
              />
              <p v-if="form.errors.password" class="mt-1 text-sm text-red-400">{{ form.errors.password }}</p>
            </div>

            <!-- Confirmar Nueva Contraseña -->
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                Confirmar Nueva Contraseña
              </label>
              <input
                v-model="passwordForm.password_confirmation"
                type="password"
                required
                class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                placeholder="••••••••"
              />
            </div>

            <!-- Botón Cambiar Contraseña -->
            <div>
              <button
                type="submit"
                :disabled="isChangingPassword"
                class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-semibold rounded-lg transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="isChangingPassword" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Cambiando...
                </span>
                <span v-else>Cambiar Contraseña</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Información del Usuario -->
      <div class="mt-8 bg-black/20 backdrop-blur-sm rounded-xl border border-purple-500/30 p-8">
        <h3 class="text-xl font-semibold text-white mb-6">Información de la Cuenta</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-gray-300">ID de Usuario</p>
              <p class="text-lg text-white">{{ user.id }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-300">Fecha de Registro</p>
              <p class="text-lg text-white">{{ formatDate(user.created_at) }}</p>
            </div>
          </div>
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-gray-300">Última Actualización</p>
              <p class="text-lg text-white">{{ formatDate(user.updated_at) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-300">Estado de la Cuenta</p>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                Activa
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'

interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

interface Props {
  user: User
  errors: Record<string, string>
}

const props = defineProps<Props>()

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const isUpdating = ref(false)
const isChangingPassword = ref(false)

const updateProfile = () => {
  isUpdating.value = true

  profileForm.put(route('profile.update.auth'), {
    onSuccess: () => {
      isUpdating.value = false
    },
    onError: () => {
      isUpdating.value = false
    }
  })
}

const changePassword = () => {
  isChangingPassword.value = true

  passwordForm.put(route('profile.password'), {
    onSuccess: () => {
      isChangingPassword.value = false
      passwordForm.reset()
    },
    onError: () => {
      isChangingPassword.value = false
    }
  })
}

const logout = () => {
  router.post(route('logout'))
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
