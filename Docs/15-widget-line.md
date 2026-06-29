El widget `line` dibuja una **línea horizontal** separadora o, si se le da altura suficiente, un **rectángulo** (con o sin relleno). Se usa habitualmente para separar visualmente la cabecera del detalle, o el detalle del pie.

```xml
<widget type="line" />
```


## ⚙️ Atributos específicos

- **`border`**: grosor del trazo o borde. Por defecto `1`. Con `border="0"` no se dibuja el contorno (útil para rectángulos de fondo puro).
- **`height`**: altura del rectángulo. Si el valor supera el doble del `border`, se dibuja un **rectángulo** de esa altura; de lo contrario, se dibuja una **línea** horizontal. Por defecto `0`.
- **`color`**: color del trazo o borde. Por defecto `black`.
- **`bgcolor`**: color de relleno interior del rectángulo. Solo aplica cuando se dibuja un rectángulo. Sin valor por defecto (sin relleno).

> **Nota**: el `height` del widget es independiente del `height` de la columna. Para una línea separadora, basta con poner `height="1"` en la columna; el widget no necesita `height` propio.


## 📌 Ejemplos

Línea separadora horizontal:
```xml
<column posx="10" posy="1" width="550" height="1">
    <widget type="line" />
</column>
```

Rectángulo de fondo gris (como banda de color para una cabecera de categoría):
```xml
<column posx="10" posy="0" width="550" height="22">
    <widget type="line" height="22" border="0" bgcolor="EBEDEF" />
</column>
```

Rectángulo con borde rojo y relleno gris:
```xml
<column posx="10" posy="100" width="550">
    <widget type="line" height="22" border="3" color="red" bgcolor="CCCCCC" />
</column>
```


## ⚠️ Orden de capas

Cezpdf dibuja los objetos en el orden en que aparecen en el XML. Si usas un rectángulo de fondo, debe declararse **antes** que las columnas de texto que quieras superponer sobre él. De lo contrario, el rectángulo tapará el texto.

```xml
<!-- primero el fondo -->
<column posx="10" posy="5" width="550" height="20">
    <widget type="line" height="20" border="0" bgcolor="4169E1" />
</column>

<!-- luego el texto encima -->
<column posx="20" posy="10" width="300">
    <widget type="label" fieldname="categoria" color="white" bold="true" />
</column>
```


## 🖥️ Comportamiento en HTML

En el visor HTML, los widgets `line` se descartan automáticamente: la tabla HTML ya dibuja sus propios bordes y separadores, por lo que las líneas del PDF no tienen equivalente directo en pantalla.
