El widget `default` muestra datos de contexto que no provienen de la fila del dataset, sino del entorno de ejecución: datos de la empresa, del usuario logueado, la fecha y hora actuales, el número de página, y cualquier objeto adicional que pases al construir la plantilla.

Hereda de `label`, por lo que admite todos sus atributos de estilo (`bold`, `size`, `color`, `align`, `bgcolor`, etc.). La diferencia es que la traducción (`translate`) queda desactivada: el valor que muestra ya es el dato real, no una clave de traducción.

```xml
<widget type="default" fieldname="company.nombre" size="12" bold="true" />
```


## 🔑 Valores disponibles en `fieldname`

### Datos de la empresa

Usa el prefijo `company.` seguido del nombre del campo del modelo `Empresa`:

```xml
<widget type="default" fieldname="company.nombre" />
<widget type="default" fieldname="company.cifnif" />
<widget type="default" fieldname="company.telefono1" />
<widget type="default" fieldname="company.email" />
```

Si la empresa no tiene nombre, muestra el texto "todas las empresas".

### Datos del usuario logueado

Usa el prefijo `user.` seguido del nombre del campo del modelo `User`:

```xml
<widget type="default" fieldname="user.nick" />
<widget type="default" fieldname="user.email" />
```

### Fecha y hora del sistema

```xml
<widget type="default" fieldname="date" />   <!-- formato d-m-Y -->
<widget type="default" fieldname="time" />   <!-- formato H:i:s -->
```

### Número de página

```xml
<widget type="default" fieldname="page" />
```

El motor incrementa automáticamente el contador de página cada vez que crea una página nueva, por lo que colocar este widget en la cabecera o el pie numera el documento sin ningún esfuerzo adicional.

### Datos adicionales de contexto

Si has pasado objetos extra al construir la plantilla (ver **[28 - Datos adicionales](28-datos-adicionales.md)**), accedes a ellos por el nombre de clave que usaste:

```xml
<!-- propiedad directa -->
<widget type="default" fieldname="filtros.texto" />

<!-- método sin parámetros -->
<widget type="default" fieldname="filtros.resumenFiltros()" />

<!-- método con parámetros -->
<widget type="default" fieldname="filtros.resumen('corto')" />
```

> Los parámetros del método se pasan al método como array. Asegúrate de que el método los recibe como `array $params`.


## 📋 Ejemplo de cabecera completa

```xml
<header height="150">
    <!-- Nombre de empresa con fondo azul -->
    <column posx="20" posy="25" width="540">
        <widget type="default" fieldname="company.nombre"
                size="12" align="left" bgcolor="4169E1" color="white" />
    </column>

    <!-- Usuario -->
    <column posx="20" posy="45" width="80">
        <widget type="default" fieldname="user.nick" size="10" />
    </column>

    <!-- Fecha y hora -->
    <column posx="450" posy="25" width="60">
        <widget type="default" fieldname="date" size="10" />
    </column>
    <column posx="510" posy="25" width="60">
        <widget type="default" fieldname="time" size="10" />
    </column>

    <!-- Número de página (solo PDF) -->
    <column posx="500" posy="47" width="60" hideonview="true">
        <widget type="default" fieldname="page" size="10" />
    </column>
</header>
```
