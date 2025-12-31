<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-[#0a1628] via-[#0d1b2e] to-[#0a1628]">
      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div>
            <h1 class="text-3xl font-bold text-white">
              <span class="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
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
        <div class="bg-black/20 backdrop-blur-sm rounded-xl border border-blue-500/30 p-8">
          <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Categoría -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Categoría *
                </label>
                <select
                  v-model="form.id_categoria"
                  required
                  class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
                  class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.id_marca }"
                  @change="onMarcaChange"
                >
                  <option value="">Selecciona una marca</option>
                  <option v-for="marca in marcas" :key="marca.id_marca" :value="marca.id_marca">
                    {{ marca.nombre }}
                  </option>
                </select>
                <p v-if="form.errors.id_marca" class="mt-1 text-sm text-red-400">{{ form.errors.id_marca }}</p>
              </div>

              <!-- Modelo -->
              <div class="relative">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Modelo
                </label>
                <input
                  v-model="modeloSearch"
                  type="text"
                  class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.id_modelo }"
                  placeholder="Buscar o crear modelo..."
                  @input="filterModelos"
                  @focus="showModelosDropdown = true"
                  @blur="handleModeloBlur"
                />
                <!-- Dropdown de modelos -->
                <div v-if="showModelosDropdown && filteredModelos.length > 0"
                     class="absolute z-10 w-full mt-1 bg-[#0a1628] border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                  <div
                    v-for="modelo in filteredModelos"
                    :key="modelo.id_modelo"
                    @mousedown="selectModelo(modelo)"
                    class="px-4 py-2 hover:bg-blue-600/20 cursor-pointer border-b border-blue-500/20 last:border-b-0 text-white"
                  >
                    <div class="font-medium">{{ modelo.nombre }}</div>
                    <div v-if="modelo.marca" class="text-xs text-gray-400">{{ modelo.marca.nombre }}</div>
                  </div>
                </div>
                <p v-if="form.errors.id_modelo" class="mt-1 text-sm text-red-400">{{ form.errors.id_modelo }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Descripción del Producto *
                </label>
                <div class="relative">
                  <input
                    v-model="form.descripcion"
                    type="text"
                    required
                    class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.descripcion }"
                    placeholder="Escribe o selecciona una descripción..."
                    @input="filterDescripciones"
                    @focus="showDescripcionesDropdown = true"
                    @blur="hideDescripcionesDropdown"
                  />
                  <div v-if="showDescripcionesDropdown && filteredDescripciones.length > 0"
                       class="absolute z-10 w-full mt-1 bg-[#0a1628] border border-blue-500/50 rounded-lg shadow-lg max-h-60 overflow-auto">
                    <div
                      v-for="desc in filteredDescripciones"
                      :key="desc"
                      @mousedown="selectDescripcion(desc)"
                      class="px-4 py-2 hover:bg-blue-600/20 cursor-pointer border-b border-blue-500/20 last:border-b-0 text-white"
                    >
                      {{ desc }}
                    </div>
                  </div>
                </div>
                <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-400">{{ form.errors.descripcion }}</p>
              </div>

              <!-- Precio de Venta -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Precio de Venta (Bs) *
                </label>
                <input
                  v-model="form.precio_venta"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-4 py-3 bg-black/30 border border-blue-500/50 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  :class="{ 'border-red-500': form.errors.precio_venta }"
                  placeholder="0.00"
                />
                <p v-if="form.errors.precio_venta" class="mt-1 text-sm text-red-400">{{ form.errors.precio_venta }}</p>
              </div>

              <!-- Imagen -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Imagen del Producto
                </label>
                <div class="flex items-center space-x-4">
                  <label class="flex-1 cursor-pointer">
                    <div class="flex items-center justify-center w-full px-4 py-3 bg-black/30 border border-purple-500/50 rounded-lg text-gray-400 hover:bg-black/40 transition-colors duration-200">
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                      </svg>
                      <span>{{ imageFileName || 'Seleccionar imagen' }}</span>
                    </div>
                    <input
                      type="file"
                      accept="image/*"
                      class="hidden"
                      @change="handleImageChange"
                    />
                  </label>
                </div>
                <p v-if="form.errors.imagen" class="mt-1 text-sm text-red-400">{{ form.errors.imagen }}</p>

                <!-- Image Preview -->
                <div v-if="imagePreview" class="mt-4">
                  <img :src="imagePreview" alt="Preview" class="max-w-xs rounded-lg border border-purple-500/30" />
                </div>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-blue-500/30">
              <Link
                :href="route('productos.index')"
                class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold rounded-lg transition-all duration-300"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
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
import { ref, watch, onMounted } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Swal from 'sweetalert2'

const route = (name: string, params?: any) => {
  return window.route(name, params)
}

interface Categoria {
  id_categoria: number
  nombre: string
}

interface Marca {
  id_marca: number
  nombre: string
}

interface Modelo {
  id_modelo: number
  nombre: string
  marca?: {
    id_marca: number
    nombre: string
  }
}

const props = defineProps<{
  categorias: Categoria[]
  marcas: Marca[]
  modelos: Modelo[]
  descripciones?: string[]
}>()

const form = useForm({
  descripcion: '',
  precio_venta: '',
  id_categoria: '',
  id_marca: '',
  id_modelo: '',
  imagen: null as File | null
})

const isSubmitting = ref(false)
const imageFileName = ref('')
const imagePreview = ref('')
const showDescripcionesDropdown = ref(false)
const filteredDescripciones = ref<string[]>(props.descripciones || [])
const modeloSearch = ref('')
const showModelosDropdown = ref(false)
const selectedModelo = ref<Modelo | null>(null)
const filteredModelos = ref<Modelo[]>([])
const modeloNuevo = ref(false)

const filterDescripciones = () => {
  if (!form.descripcion?.trim()) {
    filteredDescripciones.value = props.descripciones || []
  } else {
    const search = form.descripcion.toLowerCase()
    filteredDescripciones.value = (props.descripciones || []).filter(desc =>
      desc.toLowerCase().includes(search)
    )
  }
}

const filterModelos = () => {
  let modelos = props.modelos || []
  
  // Filtrar por marca si está seleccionada
  if (form.id_marca) {
    modelos = modelos.filter(modelo => 
      modelo.marca && modelo.marca.id_marca === parseInt(form.id_marca)
    )
  }
  
  // Filtrar por búsqueda de texto
  if (modeloSearch.value?.trim()) {
    const search = modeloSearch.value.toLowerCase()
    modelos = modelos.filter(modelo =>
      modelo.nombre.toLowerCase().includes(search)
    )
  }
  
  filteredModelos.value = modelos
  
  // Si hay un modelo seleccionado, verificar si el texto coincide
  // Si no coincide, limpiar la selección para permitir crear uno nuevo
  if (selectedModelo.value && modeloSearch.value) {
    const textoActual = modeloSearch.value.trim()
    if (textoActual !== selectedModelo.value.nombre) {
      // El usuario cambió el texto, limpiar la selección
      selectedModelo.value = null
      form.id_modelo = ''
      modeloNuevo.value = false
    }
  } else if (selectedModelo.value && !modeloSearch.value) {
    // Si el usuario borró todo el texto, limpiar la selección
    selectedModelo.value = null
    form.id_modelo = ''
    modeloNuevo.value = false
  }
}

// Watch para actualizar modelos cuando cambien los props
watch(() => props.modelos, () => {
  filterModelos()
}, { immediate: true })

// Watch para actualizar cuando cambie la marca
watch(() => form.id_marca, () => {
  filterModelos()
})

// Inicializar modelos filtrados después de que el componente esté montado
onMounted(() => {
  filterModelos()
})

const onMarcaChange = () => {
  // Limpiar modelo seleccionado cuando cambia la marca
  selectedModelo.value = null
  form.id_modelo = ''
  modeloSearch.value = ''
  showModelosDropdown.value = false
  // El filtro se actualizará automáticamente por el watch
}

const selectModelo = (modelo: Modelo) => {
  selectedModelo.value = modelo
  form.id_modelo = modelo.id_modelo.toString()
  modeloSearch.value = modelo.nombre
  showModelosDropdown.value = false
  modeloNuevo.value = false
}

const handleModeloBlur = () => {
  setTimeout(() => {
    showModelosDropdown.value = false
  }, 200)
}

const selectDescripcion = (descripcion: string) => {
  form.descripcion = descripcion
  showDescripcionesDropdown.value = false
}

const hideDescripcionesDropdown = () => {
  setTimeout(() => {
    showDescripcionesDropdown.value = false
  }, 200)
}

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    form.imagen = file
    imageFileName.value = file.name

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const submitForm = () => {
  isSubmitting.value = true

  // Verificar si hay texto en el campo de búsqueda
  if (modeloSearch.value && modeloSearch.value.trim()) {
    // Verificar si el texto coincide con el modelo seleccionado
    if (selectedModelo.value && modeloSearch.value.trim() === selectedModelo.value.nombre) {
      // El texto coincide con el modelo seleccionado, usar ese modelo
      // No hacer nada, ya está seleccionado
    } else {
      // El texto no coincide o no hay modelo seleccionado
      // Buscar si existe un modelo con ese nombre exacto en la lista completa
      const modeloExistente = props.modelos.find(m => 
        m.nombre.toLowerCase().trim() === modeloSearch.value.toLowerCase().trim()
      )
      
      if (modeloExistente) {
        // Si existe, seleccionarlo
        selectModelo(modeloExistente)
      } else {
        // Si no existe, es un modelo nuevo - necesitamos la marca
        if (!form.id_marca) {
          Swal.fire({
            title: 'Marca requerida',
            text: 'Por favor, selecciona una marca para crear un modelo nuevo.',
            icon: 'warning',
            confirmButtonText: 'OK'
          })
          isSubmitting.value = false
          return
        }
        // Limpiar selección anterior y marcar como nuevo
        selectedModelo.value = null
        form.id_modelo = ''
        modeloNuevo.value = true
      }
    }
  }

  const submitData: any = {
    descripcion: form.descripcion,
    precio_venta: form.precio_venta,
    id_categoria: form.id_categoria,
    imagen: form.imagen
  }

  // Determinar qué modelo usar
  if (modeloSearch.value && modeloSearch.value.trim()) {
    const textoModelo = modeloSearch.value.trim()
    
    // Si hay un modelo seleccionado y el texto coincide exactamente, usar ese modelo
    if (selectedModelo.value && form.id_modelo && textoModelo === selectedModelo.value.nombre) {
      submitData.id_modelo = form.id_modelo
    } else if (modeloNuevo.value && form.id_marca) {
      // Si está marcado como nuevo, enviar como modelo nuevo
      submitData.modelo_nuevo = textoModelo
      submitData.id_marca = form.id_marca
    } else if (!selectedModelo.value && form.id_marca) {
      // Si no hay modelo seleccionado pero hay texto y marca, tratarlo como nuevo
      submitData.modelo_nuevo = textoModelo
      submitData.id_marca = form.id_marca
    }
    // Si hay texto pero no hay marca, no se puede crear (ya se validó antes)
  }
  // Si no hay texto, el modelo es opcional, no se envía nada

  form.transform(() => submitData).post(route('productos.store'), {
    onSuccess: () => {
      isSubmitting.value = false
      Swal.fire({
        title: '¡Éxito!',
        text: 'Producto creado exitosamente',
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
      }).then(() => {
        router.visit(route('productos.index'))
      })
    },
    onError: () => {
      isSubmitting.value = false
    }
  })
}
</script>

