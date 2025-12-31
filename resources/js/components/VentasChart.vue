<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Ventas Semanales
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ getWeekRange(currentWeek) }}
                </p>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold text-green-600">
                    Bs {{ formatNumber(currentWeekData?.total_semana || 0) }}
                </div>
                <div class="text-sm text-gray-500">
                    Promedio: Bs {{ formatNumber(currentWeekData?.promedio_diario || 0) }}/día
                </div>
            </div>
        </div>

        <!-- Selector de semanas -->
        <div class="flex items-center justify-center mb-4">
            <div class="flex items-center space-x-4">
                <button
                    @click="previousWeek"
                    :disabled="selectedWeek >= weeks.length - 1"
                    class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <div class="text-center">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ currentWeek.label }}
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ getWeekRange(currentWeek) }}
                    </p>
                </div>

                <button
                    @click="nextWeek"
                    :disabled="selectedWeek <= 0"
                    class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                >
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Indicadores de comparación -->
        <div class="grid grid-cols-2 gap-3 mb-4" v-if="selectedWeek > 0">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-blue-600 dark:text-blue-400">vs Semana Siguiente</p>
                        <p class="text-sm font-bold" :class="comparisonColor">
                            {{ comparisonText }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-800 rounded-lg">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-green-600 dark:text-green-400">Mejor día</p>
                        <p class="text-sm font-bold text-green-900 dark:text-green-100">{{ currentWeekData?.dia_mayor_venta || 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas destacadas (solo para semana actual) -->
        <!-- Indicador de mejor día ocultado por solicitud -->

        <!-- Canvas para el gráfico -->
        <div class="relative h-40">
            <canvas ref="chartCanvas"></canvas>
        </div>

        <!-- Botón de actualización -->
        <div class="mt-4 flex justify-end">
            <button
                @click="refreshChart"
                :disabled="loading"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 disabled:opacity-50"
            >
                <svg v-if="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ loading ? 'Actualizando...' : 'Actualizar' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, computed } from 'vue'
import { Chart, registerables } from 'chart.js'
import { router } from '@inertiajs/vue3'

// Registrar todos los componentes de Chart.js
Chart.register(...registerables)

const props = defineProps({
    ventasData: {
        type: Object,
        required: false,
        default: () => ({
            labels: [],
            data: [],
            total_semana: 0,
            promedio_diario: 0,
            dia_mayor_venta: '',
            mayor_venta: 0,
            fecha_inicio: '',
            fecha_fin: '',
            semanas_atras: 0
        })
    },
    ventasSemanaAnterior: {
        type: Object,
        required: false,
        default: () => ({
            labels: [],
            data: [],
            total_semana: 0,
            promedio_diario: 0,
            dia_mayor_venta: '',
            mayor_venta: 0,
            fecha_inicio: '',
            fecha_fin: '',
            semanas_atras: 1
        })
    },
    ventasSemanaAnterior2: {
        type: Object,
        required: false,
        default: () => ({
            labels: [],
            data: [],
            total_semana: 0,
            promedio_diario: 0,
            dia_mayor_venta: '',
            mayor_venta: 0,
            fecha_inicio: '',
            fecha_fin: '',
            semanas_atras: 2
        })
    }
})

const chartCanvas = ref(null)
const chartInstance = ref(null)
const loading = ref(false)
const selectedWeek = ref(0)

// Datos de las semanas (ordenadas de más reciente a más antigua)
const weeks = ref([
    { label: 'Esta Semana', data: props.ventasData },
    { label: 'Semana Pasada', data: props.ventasSemanaAnterior },
    { label: 'Hace 2 Semanas', data: props.ventasSemanaAnterior2 }
])

// Computed properties
const currentWeek = computed(() => weeks.value[selectedWeek.value] || weeks.value[0])
const currentWeekData = computed(() => currentWeek.value?.data || {
    labels: [],
    data: [],
    total_semana: 0,
    promedio_diario: 0,
    dia_mayor_venta: '',
    mayor_venta: 0,
    fecha_inicio: '',
    fecha_fin: ''
})

const comparisonText = computed(() => {
    if (selectedWeek.value === 0) return ''

    const current = currentWeekData.value?.total_semana || 0
    const previousWeek = weeks.value[selectedWeek.value - 1]
    const previous = previousWeek?.data?.total_semana || 0

    if (previous === 0) return 'N/A'

    const difference = current - previous
    const percentage = Math.round((difference / previous) * 100)

    if (difference > 0) {
        return `+${percentage}%`
    } else if (difference < 0) {
        return `${percentage}%`
    } else {
        return '0%'
    }
})

const comparisonColor = computed(() => {
    if (selectedWeek.value === 0) return ''

    const current = currentWeekData.value?.total_semana || 0
    const previousWeek = weeks.value[selectedWeek.value - 1]
    const previous = previousWeek?.data?.total_semana || 0

    if (previous === 0) return 'text-gray-600 dark:text-gray-400'

    const difference = current - previous

    if (difference > 0) {
        return 'text-green-600 dark:text-green-400'
    } else if (difference < 0) {
        return 'text-red-600 dark:text-red-400'
    } else {
        return 'text-gray-600 dark:text-gray-400'
    }
})

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number)
}

const getWeekRange = (week) => {
    if (!week || !week.data || !week.data.fecha_inicio || !week.data.fecha_fin) {
        return 'Sin datos'
    }

    try {
        const startDate = new Date(week.data.fecha_inicio)
        const endDate = new Date(week.data.fecha_fin)

        // Validar que las fechas sean válidas
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
            return 'Sin datos'
        }

        const formatDate = (date) => {
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit'
            })
        }

        return `${formatDate(startDate)} - ${formatDate(endDate)}`
    } catch (error) {
        return 'Sin datos'
    }
}

const selectWeek = (index) => {
    selectedWeek.value = index
    nextTick(() => {
        createChart()
    })
}

const previousWeek = () => {
    if (selectedWeek.value < weeks.value.length - 1) {
        selectedWeek.value++
        nextTick(() => {
            createChart()
        })
    }
}

const nextWeek = () => {
    if (selectedWeek.value > 0) {
        selectedWeek.value--
        nextTick(() => {
            createChart()
        })
    }
}

const createChart = () => {
    if (!chartCanvas.value) return

    // Validar que existan datos antes de crear el gráfico
    if (!currentWeekData.value || !currentWeekData.value.labels || !currentWeekData.value.data) {
        return
    }

    // Destruir gráfico anterior si existe
    if (chartInstance.value) {
        chartInstance.value.destroy()
    }

    const ctx = chartCanvas.value.getContext('2d')

    const labels = currentWeekData.value.labels || []
    const data = currentWeekData.value.data || []

    chartInstance.value = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ventas (Bs)',
                data: data,
                backgroundColor: data.map((value, index) => {
                    // Color más intenso para el día con mayor venta
                    const maxValue = data.length > 0 ? Math.max(...data) : 0
                    return value === maxValue
                        ? 'rgba(34, 197, 94, 0.8)' // Verde más intenso para el mejor día
                        : 'rgba(59, 130, 246, 0.6)' // Azul para los demás días
                }),
                borderColor: data.map((value, index) => {
                    const maxValue = data.length > 0 ? Math.max(...data) : 0
                    return value === maxValue
                        ? 'rgba(34, 197, 94, 1)'
                        : 'rgba(59, 130, 246, 1)'
                }),
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Ventas: Bs ${formatNumber(context.parsed.y)}`
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6B7280',
                        callback: function(value) {
                            return 'Bs ' + formatNumber(value)
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6B7280',
                        font: {
                            weight: '500'
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    })
}

const refreshChart = () => {
    loading.value = true
    router.reload({
        onFinish: () => {
            loading.value = false
            nextTick(() => {
                createChart()
            })
        }
    })
}

onMounted(() => {
    nextTick(() => {
        createChart()
    })
})
</script>
