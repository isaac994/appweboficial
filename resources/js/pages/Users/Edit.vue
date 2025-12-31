<template>
    <AppLayout title="Editar Usuario">
        <div class="py-6">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Editar Usuario</h2>
                                <p class="text-gray-600 mt-1">Modifique la información del usuario</p>
                            </div>
                            <Link
                                :href="route('users.index')"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                Volver
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Formulario -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nombre *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    maxlength="30"
                                    @input="limitName"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.name || form.name.length >= 30 }"
                                    required
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.name" class="text-red-500 text-sm font-semibold">
                                        {{ errors.name }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.name.length >= 30 }">
                                        {{ form.name.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- Apellidos -->
                            <div>
                                <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-2">
                                    Apellidos
                                </label>
                                <input
                                    id="apellidos"
                                    v-model="form.apellidos"
                                    type="text"
                                    maxlength="30"
                                    @input="limitApellidos"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.apellidos || form.apellidos.length >= 30 }"
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.apellidos" class="text-red-500 text-sm font-semibold">
                                        {{ errors.apellidos }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.apellidos.length >= 30 }">
                                        {{ form.apellidos.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email *
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    maxlength="30"
                                    @input="limitEmail"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.email || form.email.length >= 30 }"
                                    required
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.email" class="text-red-500 text-sm font-semibold">
                                        {{ errors.email }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.email.length >= 30 }">
                                        {{ form.email.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">
                                    Teléfono
                                </label>
                                <input
                                    id="telefono"
                                    v-model="form.telefono"
                                    type="tel"
                                    maxlength="30"
                                    @input="limitTelefono"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.telefono || form.telefono.length >= 30 }"
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.telefono" class="text-red-500 text-sm font-semibold">
                                        {{ errors.telefono }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.telefono.length >= 30 }">
                                        {{ form.telefono.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- CI -->
                            <div>
                                <label for="ci" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cédula de Identidad
                                </label>
                                <input
                                    id="ci"
                                    v-model="form.ci"
                                    type="text"
                                    maxlength="30"
                                    @input="limitCI"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.ci || form.ci.length >= 30 }"
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.ci" class="text-red-500 text-sm">
                                        {{ errors.ci }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.ci.length >= 30 }">
                                        {{ form.ci.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- Gestión de Contraseña -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Gestión de Contraseña
                                </label>

                                <!-- Información general -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-gray-800">
                                                Restablecer Contraseña
                                            </h3>
                                            <div class="mt-2 text-sm text-gray-600">
                                                <p>Use el botón a continuación para restablecer la contraseña del usuario a su número de CI.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de restablecimiento -->
                                <div class="flex items-center space-x-4">
                                    <button
                                        type="button"
                                        @click="resetPassword"
                                        :disabled="isResettingPassword"
                                        class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 disabled:bg-orange-400 text-white font-medium rounded-lg transition-colors duration-200"
                                    >
                                        <svg v-if="!isResettingPassword" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ isResettingPassword ? 'Restableciendo...' : 'Restablecer Contraseña' }}
                                    </button>

                                    <span class="text-sm text-gray-500">
                                        La contraseña se establecerá como el número de CI del usuario
                                    </span>
                                </div>

                                <div v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</div>
                            </div>

                            <!-- Dirección -->
                            <div class="md:col-span-2">
                                <label for="direccion" class="block text-sm font-medium text-gray-700 mb-2">
                                    Dirección
                                </label>
                                <textarea
                                    id="direccion"
                                    v-model="form.direccion"
                                    rows="3"
                                    maxlength="500"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.direccion }"
                                ></textarea>
                                <div v-if="errors.direccion" class="text-red-500 text-sm mt-1">{{ errors.direccion }}</div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado *
                                </label>
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    @change="checkSelfDeactivation"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.estado || (props.user.id === page.props.auth.user.id && form.estado === 'inactivo') }"
                                    required
                                >
                                    <option value="activo">Activo</option>
                                    <option value="inactivo" :disabled="props.user.id === page.props.auth.user.id">Inactivo</option>
                                </select>
                                <div v-if="errors.estado" class="text-red-500 text-sm mt-1">{{ errors.estado }}</div>
                                <div v-if="props.user.id === page.props.auth.user.id && form.estado === 'inactivo'" class="text-sm text-red-600 mt-1 font-semibold">
                                    ⚠️ No puedes desactivarte a ti mismo. El estado se mantendrá como activo.
                                </div>
                            </div>

                            <!-- Roles -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Roles *
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        v-for="role in roles"
                                        :key="role.id"
                                        class="flex items-center"
                                    >
                                        <input
                                            :id="`role-${role.id}`"
                                            v-model="form.roles"
                                            :value="role.name"
                                            type="checkbox"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        />
                                        <label
                                            :for="`role-${role.id}`"
                                            class="ml-2 text-sm text-gray-700"
                                        >
                                            {{ role.name }}
                                        </label>
                                    </div>
                                </div>
                                <div v-if="errors.roles" class="text-red-500 text-sm mt-1">{{ errors.roles }}</div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('users.index')"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="processing"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
                            >
                                <div v-if="processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                {{ processing ? 'Actualizando...' : 'Actualizar Usuario' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
    user: Object,
    roles: Array
})

const page = usePage()

const isResettingPassword = ref(false)

const form = useForm({
    name: props.user.name || '',
    apellidos: props.user.apellidos || '',
    email: props.user.email || '',
    telefono: props.user.telefono || '',
    ci: props.user.ci || '',
    direccion: props.user.direccion || '',
    estado: props.user.estado || 'activo',
    roles: props.user.roles ? props.user.roles.map(role => role.name) : []
})

const limitName = (event) => {
    if (form.name.length > 30) {
        form.name = form.name.substring(0, 30)
        event.target.value = form.name
    }
}

const limitApellidos = (event) => {
    if (form.apellidos.length > 30) {
        form.apellidos = form.apellidos.substring(0, 30)
        event.target.value = form.apellidos
    }
}

const limitEmail = (event) => {
    if (form.email.length > 30) {
        form.email = form.email.substring(0, 30)
        event.target.value = form.email
    }
}

const limitTelefono = (event) => {
    if (form.telefono.length > 30) {
        form.telefono = form.telefono.substring(0, 30)
        event.target.value = form.telefono
    }
}

const limitCI = (event) => {
    if (form.ci.length > 30) {
        form.ci = form.ci.substring(0, 30)
        event.target.value = form.ci
    }
}

const checkSelfDeactivation = () => {
    // Si el usuario intenta desactivarse a sí mismo, revertir el cambio
    if (props.user.id === page.props.auth.user.id && form.estado === 'inactivo') {
        Swal.fire({
            title: 'Error',
            text: 'No puedes desactivarte a ti mismo.',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Entendido'
        })
        form.estado = 'activo'
    }
}

const submit = () => {
    // Prevenir que un usuario se desactive a sí mismo antes de enviar
    if (props.user.id === page.props.auth.user.id && form.estado === 'inactivo') {
        Swal.fire({
            title: 'Error',
            text: 'No puedes desactivarte a ti mismo.',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Entendido'
        })
        form.estado = 'activo'
        return
    }
    form.put(route('users.update', props.user.id), {
        onSuccess: () => {
            Swal.fire({
                title: '¡Actualización Exitosa!',
                text: 'Usuario actualizado correctamente',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Entendido'
            })
        }
    })
}

const resetPassword = () => {
    // Usar SweetAlert2 para confirmación
    Swal.fire({
        title: '¿Restablecer Contraseña?',
        html: `
            <div class="text-left">
                <p class="mb-3">¿Está seguro de que desea restablecer la contraseña del usuario <strong>${props.user.name}</strong>?</p>
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                    <p class="text-sm text-orange-800">
                        La contraseña se establecerá como el número de CI del usuario.
                    </p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, restablecer',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            isResettingPassword.value = true

            // Usar fetch con token CSRF
            fetch(route('users.reset-password', props.user.id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                // Importante: incluir cookies de sesión para que el backend
                // reconozca al usuario autenticado y no redirija ni marque 419
                credentials: 'same-origin',
                body: JSON.stringify({
                    password: form.ci
                })
            })
            .then(async response => {
                // Manejar errores HTTP primero
                if (response.status === 419) {
                    throw new Error('CSRF token inválido. Por favor, recarga la página e intenta nuevamente.')
                }

                // Intentar parsear la respuesta JSON
                let data
                try {
                    data = await response.json()
                } catch (e) {
                    // Si no es JSON, crear un objeto de error
                    if (!response.ok) {
                        throw new Error(`Error del servidor (${response.status}). Por favor, intenta nuevamente.`)
                    }
                    throw new Error('Error al procesar la respuesta del servidor')
                }

                if (!response.ok && response.status !== 200) {
                    throw new Error(data.message || `Error del servidor (${response.status})`)
                }

                return data
            })
            .then(data => {
                isResettingPassword.value = false

                // Verificar si la contraseña ya estaba establecida
                if (data.already_set) {
                    Swal.fire({
                        title: 'Contraseña ya establecida',
                        html: `
                            <div class="text-left">
                                <p class="mb-3">${data.message}</p>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <p class="text-sm text-blue-800">
                                        <strong>Información:</strong> La contraseña actual del usuario ya coincide con su número de CI (${form.ci}).
                                    </p>
                                </div>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'Entendido'
                    })
                    return
                }

                if (data.success) {
                    Swal.fire({
                        title: '¡Contraseña Restablecida!',
                        html: `
                            <div class="text-left">
                                <p class="mb-3">La contraseña ha sido restablecida exitosamente.</p>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                    <p class="text-sm text-green-800">
                                        <strong>Nueva contraseña:</strong> ${form.ci}
                                    </p>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Entendido'
                    })
                } else {
                    throw new Error(data.message || 'Error al restablecer contraseña')
                }
            })
            .catch(error => {
                isResettingPassword.value = false
                console.error('Error:', error)

                let errorMessage = error.message || 'Ocurrió un error al restablecer la contraseña'

                // Mensaje más claro para errores CSRF
                if (errorMessage.includes('CSRF') || errorMessage.includes('419')) {
                    errorMessage = 'Error de seguridad: Tu sesión ha expirado. Por favor, recarga la página e intenta nuevamente.'
                }

                Swal.fire({
                    title: 'Error',
                    html: `
                        <div class="text-left">
                            <p>${errorMessage}</p>
                            ${errorMessage.includes('sesión') ? '<p class="mt-2 text-sm text-gray-600">Si el problema persiste, cierra sesión y vuelve a iniciar sesión.</p>' : ''}
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Entendido'
                })
            })
        }
    })
}

const { errors, processing } = form
</script>
