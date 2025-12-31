<template>
    <AppLayout title="Crear Usuario">
        <div class="py-6">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Crear Nuevo Usuario</h2>
                                <p class="text-gray-600 mt-1">Complete la información del usuario</p>
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
                        <!-- Mensaje de error general -->
                        <div v-if="hasErrors" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Por favor corrige los siguientes errores:
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li v-for="(error, field) in errors" :key="field">
                                                <strong>{{ fieldLabel(field) }}:</strong>
                                                {{ Array.isArray(error) ? error[0] : error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    :class="{ 'border-red-500': errors.name || form.name.length >= 30 }"
                                    required
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.name" class="text-red-500 text-sm font-semibold">
                                        {{ firstError(errors.name) }}
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    :class="{ 'border-red-500': errors.apellidos || form.apellidos.length >= 30 }"
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.apellidos" class="text-red-500 text-sm font-semibold">
                                        {{ firstError(errors.apellidos) }}
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    :class="{ 'border-red-500': errors.email || form.email.length >= 30 }"
                                    required
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.email" class="text-red-500 text-sm font-semibold">
                                        {{ firstError(errors.email) }}
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    :class="{ 'border-red-500': errors.telefono || form.telefono.length >= 30 }"
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.telefono" class="text-red-500 text-sm font-semibold">
                                        {{ firstError(errors.telefono) }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.telefono.length >= 30 }">
                                        {{ form.telefono.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- CI -->
                            <div>
                                <label for="ci" class="block text-sm font-medium text-gray-700 mb-2">
                                    Cédula de Identidad *
                                </label>
                                <input
                                    id="ci"
                                    v-model="form.ci"
                                    type="text"
                                    maxlength="30"
                                    @input="limitCI"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    :class="{ 'border-red-500': errors.ci || form.ci.length >= 30 }"
                                    required
                                />
                                <div class="flex justify-between items-center mt-1">
                                    <div v-if="errors.ci" class="text-red-500 text-sm font-semibold">
                                        {{ firstError(errors.ci) }}
                                    </div>
                                    <div v-else class="text-sm text-gray-500" :class="{ 'text-red-500 font-semibold': form.ci.length >= 30 }">
                                        {{ form.ci.length }}/30 caracteres
                                    </div>
                                </div>
                            </div>

                            <!-- Contraseña automática -->
                            <div class="md:col-span-2">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">Contraseña Automática</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <p>La contraseña se generará automáticamente usando el número de CI.</p>
                                                <p class="mt-1">
                                                    <strong>Contraseña por defecto:</strong>
                                                    <span class="font-mono bg-blue-100 px-2 py-1 rounded">{{ form.ci || '[Número de CI]' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder-gray-400"
                                    :class="{ 'border-red-500': errors.direccion }"
                                ></textarea>
                                <div v-if="errors.direccion" class="text-red-500 text-sm mt-1">{{ firstError(errors.direccion) }}</div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado *
                                </label>
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 bg-white"
                                    :class="{ 'border-red-500': errors.estado }"
                                    required
                                >
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                <div v-if="errors.estado" class="text-red-500 text-sm mt-1">{{ firstError(errors.estado) }}</div>
                            </div>

                            <!-- Roles -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Roles *</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        v-for="role in props.roles || []"
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
                                <div v-if="errors.roles" class="text-red-500 text-sm mt-1">{{ firstError(errors.roles) }}</div>
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
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
                            >
                                <div v-if="processing" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                {{ processing ? 'Creando...' : 'Crear Usuario' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const props = defineProps({
    roles: {
        type: Array,
        default: () => []
    }
})

const form = useForm({
    name: '',
    apellidos: '',
    email: '',
    telefono: '',
    ci: '',
    direccion: '',
    estado: 'activo',
    roles: []
})

const { errors, processing } = form

const hasErrors = computed(() => errors && Object.keys(errors).length > 0)

const firstError = (fieldErrors) => Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors

const fieldLabel = (field) => {
    const map = { ci: 'Cédula de Identidad', email: 'Correo electrónico', name: 'Nombre', roles: 'Roles' }
    return map[field] || field
}

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

const submit = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: '¡Usuario Creado!',
                text: 'Usuario creado exitosamente',
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Entendido'
            })
        },
        onError: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }
    })
}
</script>
