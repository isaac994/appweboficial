# Logo del Sistema

## Instrucciones para subir el logo

Para que el logo aparezca en todos los reportes PDF, sigue estos pasos:

### 1. Preparar la imagen
- Formato recomendado: PNG (con transparencia) o JPG
- Tamaño recomendado: 200x200 píxeles o similar (cuadrado)
- Nombre del archivo: `logo.png`

### 2. Subir la imagen
Copia tu imagen del logo a esta carpeta con el nombre `logo.png`:
```
public/images/logo.png
```

### 3. Verificar
Una vez subido el archivo, el logo aparecerá automáticamente en todos los reportes:
- ✅ Reporte de Ventas
- ✅ Reporte de Clientes  
- ✅ Reporte de Proveedores
- ✅ Reporte de Productos
- ✅ Reporte de Compras

### Estructura actual
```
public/images/
├── README.md (este archivo)
└── logo.png (tu logo aquí)
```

### Notas técnicas
- El logo se redimensiona automáticamente a 80x80 píxeles en los PDFs
- Se mantiene la proporción original con `object-fit: contain`
- El logo aparece a la izquierda del título en el header de cada reporte

