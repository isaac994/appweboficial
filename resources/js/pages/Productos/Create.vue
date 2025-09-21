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
                Crear Nuevo Producto
              </span>
            </h1>
            <p class="text-gray-300 mt-2">Agrega un nuevo producto al inventario</p>
          </div>
          <Link
            :href="route('productos.index')"
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
          <form @submit.prevent="submitForm" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Nombre del Producto -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Nombre del Producto *
                </label>
                <input
                  v-model="form.nombre"
                  type="text"
                  required
                  maxlength="50"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.nombre }"
                  placeholder="Nombre del producto (máximo 50 caracteres)"
                />
                <div class="flex justify-between items-center mt-1">
                  <p v-if="form.errors.nombre" class="text-sm text-red-400">{{ form.errors.nombre }}</p>
                  <p class="text-sm text-gray-400">{{ form.nombre.length }}/50 caracteres</p>
                </div>
              </div>

              <!-- Descripción -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Descripción
                </label>
                <textarea
                  v-model="form.descripcion"
                  rows="3"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Describe las características del producto..."
                ></textarea>
              </div>


              <!-- Precio de Venta -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Precio de Venta (Bs) *
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-3 text-gray-400">Bs</span>
                  <input
                    v-model="form.precio_venta"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full pl-8 pr-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.precio_venta }"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="form.errors.precio_venta" class="mt-1 text-sm text-red-400">{{ form.errors.precio_venta }}</p>
              </div>

              <!-- Categoría -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Categoría *
                </label>
                <select
                  v-model="form.id_categoria"
                  required
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.id_categoria }"
                >
                  <option value="">Selecciona una categoría</option>
                  <option v-for="categoria in categorias" :key="categoria.id_categoria" :value="categoria.id_categoria">
                    {{ categoria.nombre }}
                  </option>
                </select>
                <p v-if="form.errors.id_categoria" class="mt-1 text-sm text-red-400">{{ form.errors.id_categoria }}</p>
              </div>

              <!-- Marca -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Marca
                </label>
                <select
                  v-model="form.id_marca"
                  class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.id_marca }"
                >
                  <option value="">Selecciona una marca</option>
                  <option v-for="marca in marcas" :key="marca.id_marca" :value="marca.id_marca">
                    {{ marca.nombre }}
                  </option>
                </select>
                <p v-if="form.errors.id_marca" class="mt-1 text-sm text-red-400">{{ form.errors.id_marca }}</p>
              </div>



              <!-- Imagen del Producto -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Imagen del Producto
                </label>
                <div class="flex items-center space-x-4">
                  <div class="flex-1">
                    <input
                      ref="fileInput"
                      type="file"
                      accept="image/*"
                      @change="handleFileSelect"
                      class="hidden"
                    />
                    <button
                      type="button"
                      @click="$refs.fileInput.click()"
                      class="w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-white hover:bg-black/50 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-300"
                    >
                      <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>{{ selectedFileName || 'Seleccionar imagen' }}</span>
                      </div>
                    </button>
                  </div>
                  <div v-if="selectedFileName" class="flex-shrink-0">
                    <button
                      type="button"
                      @click="clearFile"
                      class="px-3 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-300"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>
                <p v-if="form.errors.imagen" class="mt-1 text-sm text-red-400">{{ form.errors.imagen }}</p>
                <p class="mt-1 text-sm text-gray-400">Selecciona una imagen desde tu galería (opcional)</p>
              </div>

              <!-- Vista Previa de Imagen -->
              <div v-if="imagePreview" class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">Vista Previa</label>
                <div class="w-32 h-32 border-2 border-purple-500/50 rounded-lg overflow-hidden">
                  <img
                    :src="imagePreview"
                    :alt="form.nombre"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
            </div>


            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-purple-500/30">
              <Link
                :href="route('productos.index')"
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
                <span v-else>Guardar Producto</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'

interface Categoria {
  id_categoria: number
  nombre: string
  descripcion: string | null
}

interface Marca {
  id_marca: number
  nombre: string
  pais_origen: string | null
}

interface Props {
  categorias: Categoria[]
  marcas: Marca[]
  errors: Record<string, string>
}

const props = defineProps<Props>()

const form = useForm({
  nombre: '',
  descripcion: '',
  precio_venta: '',
  id_categoria: '',
  id_marca: '',
  imagen: null as File | null
})

const isSubmitting = ref(false)
const selectedFileName = ref('')
const imagePreview = ref('')

const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]

  if (file) {
    // Validar que sea una imagen
    if (!file.type.startsWith('image/')) {
      alert('Por favor selecciona solo archivos de imagen')
      return
    }

    // Validar tamaño (máximo 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('La imagen no debe superar los 5MB')
      return
    }

    form.imagen = file
    selectedFileName.value = file.name

    // Crear vista previa
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const clearFile = () => {
  form.imagen = null
  selectedFileName.value = ''
  imagePreview.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const fileInput = ref<HTMLInputElement>()

const submitForm = () => {
  isSubmitting.value = true

  form.post(route('productos.store'), {
    onSuccess: () => {
      isSubmitting.value = false
      Swal.fire({
        title: '¡Éxito!',
        text: 'Producto creado exitosamente',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      }).then(() => {
        router.visit(route('productos.index'))
      })
    },
    onError: (errors) => {
      isSubmitting.value = false

      // Mostrar mensaje flotante para duplicidad
      if (errors.duplicidad) {
        Swal.fire({
          title: '⚠️ Producto Duplicado',
          html: `
            <div style="text-align: left;">
              <p style="margin-bottom: 10px;"><strong>${errors.duplicidad}</strong></p>
              <p style="color: #666; font-size: 14px;">Por favor, cambia el nombre, marca o categoría para crear un producto único.</p>
            </div>
          `,
          icon: 'warning',
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#ef4444',
          toast: true,
          position: 'top',
          showConfirmButton: true,
          timer: 6000,
          timerProgressBar: true,
          allowOutsideClick: false,
          customClass: {
            popup: 'swal-popup-duplicate',
            title: 'swal-title-duplicate',
            content: 'swal-content-duplicate'
          }
        })
      }
    }
  })
}
</script>

<style scoped>
/* Estilos personalizados para SweetAlert2 */
:deep(.swal-popup-duplicate) {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border: 2px solid #ef4444;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
}

:deep(.swal-title-duplicate) {
  color: #dc2626;
  font-weight: 700;
  font-size: 1.2rem;
}

:deep(.swal-content-duplicate) {
  color: #374151;
  font-size: 1rem;
  line-height: 1.5;
}
</style>
