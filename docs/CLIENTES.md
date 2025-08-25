# Módulo de Gestión de Clientes

## Descripción
El módulo de gestión de clientes permite administrar toda la información relacionada con los clientes de la tienda de celulares, incluyendo datos personales, historial de compras, y análisis de fidelización.

## Características Principales

### 🎯 Funcionalidades del CRUD
- **Crear**: Registro de nuevos clientes con información completa
- **Leer**: Visualización detallada de perfiles de clientes
- **Actualizar**: Modificación de datos del cliente
- **Eliminar**: Eliminación segura (con validación de dependencias)

### 📊 Campos del Cliente
- **Información Personal**:
  - Nombre completo (obligatorio)
  - Teléfono
  - Correo electrónico
  - Dirección
  - Fecha de nacimiento
  - Género (Masculino, Femenino, Otro)

- **Información del Sistema**:
  - ID único del cliente
  - Fecha de registro
  - Estado de fidelización automático

### 🏆 Sistema de Fidelización
El sistema clasifica automáticamente a los clientes según su historial de compras:

- **Bronce**: Clientes sin ventas o con menos de $100
- **Plata**: Clientes con compras entre $100 y $499
- **Oro**: Clientes con compras entre $500 y $999
- **Premium**: Clientes con compras de $1000 o más

### 📈 Estadísticas y Análisis
- Total de ventas por cliente
- Monto total gastado
- Promedio por transacción
- Historial de compras
- Última fecha de compra
- Tendencias de comportamiento

## Estructura de Archivos

### Frontend (Vue.js)
```
resources/js/pages/Clientes/
├── Index.vue          # Lista de clientes con filtros
├── Create.vue         # Formulario de creación
├── Edit.vue          # Formulario de edición
├── Show.vue          # Vista detallada del cliente
└── Dashboard.vue     # Dashboard con estadísticas
```

### Componentes Reutilizables
```
resources/js/components/ui/cliente/
├── ClienteCard.vue   # Tarjeta de cliente reutilizable
└── index.ts          # Exportaciones del módulo
```

### Backend (Laravel)
```
app/
├── Http/Controllers/
│   └── ClienteController.php    # Controlador principal
├── Models/
│   └── Cliente.php              # Modelo del cliente
└── database/migrations/
    ├── create_clientes_table.php
    └── add_fecha_nacimiento_and_genero_to_clientes_table.php
```

## Rutas Disponibles

```php
// Rutas del módulo de clientes
Route::resource('clientes', ClienteController::class);
Route::get('/clientes-dashboard', [ClienteController::class, 'dashboard'])->name('clientes.dashboard');
```

### Endpoints Disponibles
- `GET /clientes` - Lista de clientes
- `GET /clientes/create` - Formulario de creación
- `POST /clientes` - Crear cliente
- `GET /clientes/{id}` - Ver cliente
- `GET /clientes/{id}/edit` - Formulario de edición
- `PUT /clientes/{id}` - Actualizar cliente
- `DELETE /clientes/{id}` - Eliminar cliente
- `GET /clientes-dashboard` - Dashboard de estadísticas

## Funcionalidades Avanzadas

### 🔍 Filtros y Búsqueda
- **Búsqueda por texto**: Nombre, teléfono o email
- **Filtro por género**: Masculino, Femenino, Otro
- **Filtro por fidelización**: Bronce, Plata, Oro, Premium
- **Ordenamiento**: Por nombre, fecha de registro, total de ventas, monto gastado

### 📊 Dashboard de Clientes
- Estadísticas generales en tiempo real
- Distribución por nivel de fidelización
- Distribución por género
- Top 5 clientes por ventas
- Clientes recientes
- Acciones rápidas

### 🎨 Interfaz de Usuario
- Diseño responsive con Tailwind CSS
- Componentes reutilizables
- Navegación intuitiva
- Indicadores visuales de estado
- Iconos y elementos gráficos

## Validaciones y Seguridad

### Validaciones del Frontend
- Campos obligatorios marcados
- Validación de formato de email
- Validación de formato de teléfono
- Mensajes de error contextuales

### Validaciones del Backend
- Validación de datos con Laravel Validator
- Verificación de unicidad de email
- Validación de formato de fecha
- Validación de valores de género

### Seguridad
- Middleware de autenticación
- Validación de permisos
- Protección CSRF
- Sanitización de datos

## Integración con Otros Módulos

### 🔗 Relaciones
- **Ventas**: Un cliente puede tener múltiples ventas
- **Detalle de Ventas**: Cada venta puede contener múltiples productos
- **Usuarios**: Las ventas se asocian con usuarios del sistema

### 📋 Flujo de Trabajo
1. **Registro del Cliente**: Se crea el perfil básico
2. **Primera Venta**: Se registra la primera transacción
3. **Seguimiento**: Se actualiza el estado de fidelización
4. **Análisis**: Se generan estadísticas y reportes

## Uso del Componente ClienteCard

```vue
<template>
  <ClienteCard 
    :cliente="cliente" 
    @delete="handleDelete" 
  />
</template>

<script setup>
import { ClienteCard } from '@/components/ui/cliente'

const handleDelete = (cliente) => {
  // Lógica para eliminar cliente
}
</script>
```

## Personalización y Extensión

### 🎨 Temas y Estilos
- Colores personalizables por nivel de fidelización
- Iconos SVG integrados
- Transiciones y animaciones CSS
- Responsive design para móviles

### 🔧 Configuración
- Niveles de fidelización configurables
- Campos personalizables
- Validaciones ajustables
- Integración con sistemas externos

## Mantenimiento y Soporte

### 🐛 Solución de Problemas
- Verificación de dependencias antes de eliminar
- Logs de auditoría para cambios
- Validación de integridad de datos
- Manejo de errores robusto

### 📈 Mejoras Futuras
- Integración con CRM externos
- Sistema de notificaciones
- Reportes avanzados
- API para aplicaciones móviles
- Integración con redes sociales

## Contribución

Para contribuir al módulo de clientes:

1. Sigue las convenciones de código establecidas
2. Agrega tests para nuevas funcionalidades
3. Documenta los cambios realizados
4. Verifica la compatibilidad con otros módulos
5. Actualiza la documentación según sea necesario

---

**Nota**: Este módulo está diseñado para ser escalable y mantenible, siguiendo las mejores prácticas de desarrollo web moderno.
