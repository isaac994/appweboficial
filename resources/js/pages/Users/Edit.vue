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
                                    Nombre Completo *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': errors.name }"
                                    required
                                />
                                <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': errors.email }"
                                    required
                                />
                                <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</div>
                            </div>

                            <!-- Contraseña -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nueva Contraseña
                                    <span class="text-gray-500 text-sm">(dejar vacío para mantener la actual)</span>
                                </label>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': errors.password }"
                                />
                                <div v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmar Nueva Contraseña
                                </label>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': errors.password_confirmation }"
                                />
                                <div v-if="errors.password_confirmation" class="text-red-500 text-sm mt-1">
                                    {{ errors.password_confirmation }}
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado *
                                </label>
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': errors.estado }"
                                    required
                                >
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                <div v-if="errors.estado" class="text-red-500 text-sm mt-1">{{ errors.estado }}</div>
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
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    roles: Array
})

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    estado: props.user.estado,
    roles: props.user.roles.map(role => role.name)
})

const submit = () => {
    form.put(route('users.update', props.user.id))
}

const { errors, processing } = form
</script>
