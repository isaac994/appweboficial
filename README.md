# Sistema de Gestión de Ventas e Inventario - Tienda de Celulares

## 📱 Descripción

Sistema web completo para la gestión de ventas e inventario de una tienda de celulares y accesorios. Desarrollado con Laravel 12, Vue 3, TypeScript, Inertia.js y Tailwind CSS.

## ✨ Características

### 🎨 Diseño Digital Neon
- **Tema Moderno**: Diseño con colores neón y gradientes vibrantes
- **Interfaz Responsiva**: Optimizada para desktop, tablet y móvil
- **UX/UI Profesional**: Interfaz intuitiva y atractiva para el sector tecnológico

### 📊 Módulo de Productos (Fase 1)
- ✅ **CRUD Completo**: Crear, Leer, Actualizar, Eliminar productos
- ✅ **Gestión de Categorías**: Smartphones, Tablets, Accesorios, etc.
- ✅ **Gestión de Marcas**: Apple, Samsung, Xiaomi, Huawei, etc.
- ✅ **Gestión de Proveedores**: Información completa de proveedores
- ✅ **Imágenes de Productos**: URLs de imágenes con vista previa
- ✅ **Filtros Avanzados**: Por nombre, categoría, marca
- ✅ **Paginación**: Navegación eficiente entre productos
- ✅ **Validaciones**: Frontend y backend con mensajes de error

### 🛠️ Tecnologías Utilizadas

#### Backend
- **Laravel 12**: Framework PHP moderno
- **MySQL**: Base de datos relacional
- **Eloquent ORM**: Mapeo objeto-relacional
- **Inertia.js**: SPA sin API REST

#### Frontend
- **Vue 3**: Framework JavaScript progresivo
- **TypeScript**: Tipado estático
- **Inertia.js**: Navegación SPA
- **Tailwind CSS**: Framework CSS utility-first

## 🚀 Instalación

### Prerrequisitos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <url-del-repositorio>
cd appweb_oficial
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias Node.js**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos**
Editar el archivo `.env` con tus credenciales de MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

6. **Ejecutar migraciones y seeders**
```bash
php artisan migrate:fresh --seed
```

7. **Compilar assets**
```bash
npm run build
```

8. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

## 📁 Estructura del Proyecto

```
appweb_oficial/
├── app/
│   ├── Http/Controllers/
│   │   └── ProductoController.php    # Controlador de productos
│   └── Models/
│       ├── Producto.php              # Modelo de productos
│       ├── Categoria.php             # Modelo de categorías
│       ├── Marca.php                 # Modelo de marcas
│       └── Proveedor.php             # Modelo de proveedores
├── database/
│   ├── migrations/                   # Migraciones de base de datos
│   └── seeders/                      # Seeders con datos de prueba
├── resources/
│   └── js/pages/Productos/           # Páginas Vue de productos
│       ├── Index.vue                 # Listado de productos
│       ├── Create.vue                # Crear producto
│       └── Edit.vue                  # Editar producto
└── routes/
    └── web.php                       # Rutas de la aplicación
```

## 🗄️ Base de Datos

### Tablas Principales

#### `categorias`
- `id_categoria` (smallserial, PK)
- `nombre` (varchar(60), NOT NULL)
- `descripcion` (varchar(255), nullable)

#### `marcas`
- `id_marca` (smallserial, PK)
- `nombre` (varchar(60), NOT NULL)
- `pais_origen` (varchar(60), nullable)

#### `proveedores`
- `id_proveedor` (bigserial, PK)
- `nombre` (varchar(150), NOT NULL)
- `telefono` (varchar(25), nullable)
- `direccion` (varchar(255), nullable)
- `correo` (varchar(150), nullable)

#### `productos`
- `id_producto` (bigserial, PK)
- `nombre` (varchar(150), NOT NULL)
- `descripcion` (text, nullable)
- `estado` (enum('activo','inactivo'), DEFAULT 'activo')
- `precio_venta` (decimal(10,2), NOT NULL)
- `id_categoria` (smallint, FK → categorias)
- `id_marca` (smallint, FK → marcas, nullable)
- `id_proveedor` (bigint, FK → proveedores, nullable)
- `img_url` (varchar(255), nullable)

## 🎯 Funcionalidades Implementadas

### Gestión de Productos
- ✅ Listado con paginación y filtros
- ✅ Crear nuevos productos
- ✅ Editar productos existentes
- ✅ Eliminar productos
- ✅ Vista previa de imágenes
- ✅ Validaciones completas

### Filtros y Búsqueda
- ✅ Búsqueda por nombre de producto
- ✅ Filtro por categoría
- ✅ Filtro por marca
- ✅ Limpiar filtros

### Validaciones
- ✅ Campos obligatorios
- ✅ Validación de precios (números positivos)
- ✅ Validación de URLs de imágenes
- ✅ Validación de relaciones (categorías, marcas, proveedores)

## 🎨 Diseño Digital Neon

### Paleta de Colores
- **Fondo**: Gradiente slate-900 → purple-900 → slate-900
- **Acentos**: Purple-400 → pink-400 (gradientes)
- **Elementos**: Black/20 con backdrop-blur
- **Bordes**: Purple-500/30

### Características Visuales
- **Efectos Neón**: Bordes y sombras con colores vibrantes
- **Animaciones**: Transiciones suaves y efectos hover
- **Glassmorphism**: Efectos de cristal esmerilado
- **Responsive**: Diseño adaptativo para todos los dispositivos

## 🚧 Próximas Fases

### Fase 2: Módulo de Usuarios y Autenticación
- [ ] Sistema de registro y login
- [ ] Roles y permisos
- [ ] Dashboard de usuario

### Fase 3: Módulo de Clientes
- [ ] Gestión de clientes
- [ ] Historial de compras
- [ ] Perfiles de cliente

### Fase 4: Módulo de Ventas
- [ ] Crear ventas
- [ ] Carrito de compras
- [ ] Facturación

### Fase 5: Módulo de Inventario
- [ ] Control de stock
- [ ] Alertas de inventario bajo
- [ ] Reportes de inventario

### Fase 6: Dashboard y Reportes
- [ ] Estadísticas de ventas
- [ ] Productos más vendidos
- [ ] Reportes financieros

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Contacto

- **Desarrollador**: [Tu Nombre]
- **Email**: [tu-email@ejemplo.com]
- **Proyecto**: [URL del repositorio]

---

⭐ Si te gusta este proyecto, ¡dale una estrella! 
