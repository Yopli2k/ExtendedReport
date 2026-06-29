El widget `label` es el más utilizado. Muestra cualquier dato de texto: un literal fijo, el valor de un campo del modelo, o un texto traducido al idioma del usuario.

```xml
<widget type="label" value="..." />
<widget type="label" fieldname="..." />
```


## 🎨 Atributos de estilo

- **`align`**: alineación horizontal del texto. Por defecto `left`. Valores: `left`, `center`, `right`.
- **`bold`**: texto en negrita. Por defecto `false`.
- **`italic`**: texto en cursiva. Por defecto `false`.
- **`underline`**: texto subrayado. Por defecto `false`.
- **`size`**: tamaño de la fuente. Por defecto `10`.
- **`bgcolor`**: color de fondo de la celda. Mismo formato que `color` (nombre o hexadecimal). Por defecto ninguno.


## 📝 Texto fijo

Usa el atributo `value` para mostrar un texto literal. Con `translate="true"` el texto se pasa por el sistema de traducción de FacturaScripts antes de imprimirse.

```xml
<!-- texto fijo sin traducir -->
<column posx="20" posy="10" width="200">
    <widget type="label" value="Listado de clientes" bold="true" size="14" />
</column>

<!-- texto fijo traducido al idioma del usuario -->
<column posx="20" posy="30" width="100">
    <widget type="label" value="total" translate="true" align="right" bold="true" />
</column>
```

Atributo:
- **`translate`**: si es `true`, traduce el `value` con el idioma del usuario. Por defecto `false`.


## 🔁 Texto variable

Usa el atributo `fieldname` para mostrar el valor de un campo del registro de datos. Se usa principalmente en la banda `detail`.

```xml
<column posx="20" width="50">
    <widget type="label" fieldname="codigo" bold="true" />
</column>

<column posx="80" width="200">
    <widget type="label" fieldname="nombre" italic="true" />
</column>
```

También puedes acceder a arrays y métodos del registro:

```xml
<!-- array -->
<column posx="200" width="60">
    <widget type="label" fieldname="diasSemana[0]" />
</column>

<!-- llamada a método -->
<column posx="270" width="120">
    <widget type="label" fieldname="getDescripcion()" />
</column>
```


## 📄 Texto multilínea

El texto se ajusta automáticamente al `width` de la columna en el PDF: si el texto es más largo que el ancho disponible, salta de línea respetando el `height` de la columna.

En el **visor HTML**, si el valor contiene saltos de línea (`\n`) y quieres que se muestren apilados en pantalla, añade el atributo `prewrap`:

- **`prewrap`**: si es `true`, preserva los saltos de línea en HTML (`white-space: pre-line`). Por defecto `false`. El PDF lo ignora.

```xml
<column posx="20" posy="10" width="300">
    <widget type="label" fieldname="resumenFiltros" prewrap="true" />
</column>
```


## 📌 Ejemplos combinados

Cabecera de columna con fondo azul y texto blanco:
```xml
<column posx="460" posy="115" width="80">
    <widget type="label" value="total" translate="true"
            bold="true" size="13" align="right"
            bgcolor="4169E1" color="white" />
</column>
```

Detalle con texto en cursiva y color gris:
```xml
<column posx="80" width="250">
    <widget type="label" fieldname="observaciones" italic="true" color="silver" />
</column>
```
