# ExtendedReport — Widgets

Un **widget** es el contenido que se dibuja dentro de una `<column>`. El atributo `type` determina qué clase se usa: el motor concatena `Widget` + el `type` capitalizado, así que `type="label"` instancia `WidgetLabel`, `type="number"` instancia `WidgetNumber`, etc. Todas las clases viven en `Lib/WidgetReport/`.

Este documento es la referencia de cada tipo y sus atributos. La estructura general del XML está en [02 - Estructura del XML](02-estructura-xml.md).


## Jerarquía de clases

```
WidgetItem (abstracta)
├── WidgetLine            type="line"
├── WidgetImage           type="image"
│   └── WidgetImageproduct type="imageproduct"
└── WidgetLabel           type="label"
    ├── WidgetDefault     type="default"
    └── WidgetNumber      type="number"
        └── WidgetCalculated type="calculated"
```

La herencia importa porque los atributos se **acumulan**: un `number` admite todo lo de `label` más lo suyo; un `calculated` admite todo lo de `number` más su `operator`.


## Atributos comunes a todos los widgets (`WidgetItem`)

- **`type`** — obligatorio. Tipo de widget.
- **`value`** (por defecto `''`) — valor/texto literal a mostrar.
- **`fieldname`** (por defecto `''`) — campo del dato cuyo valor se muestra (ver resolución abajo).
- **`color`** (por defecto `black`) — color del texto/trazo. Nombre predefinido o hexadecimal.
- **`class`** (por defecto `''`) — clases CSS adicionales que se añaden al elemento en la salida **HTML**. Pensado para usar utilidades de Bootstrap (p. ej. `class="h2"` para que el título aparezca con tamaño de encabezado, `class="fw-bold text-end"` para negrita alineada a la derecha). El PDF ignora este atributo completamente.

### Colores admitidos

Se acepta un **nombre** predefinido o un valor **hexadecimal** (`RRGGBB`, con o sin `#`).

- Nombres: `black`, `blue`, `green`, `orange`, `red`, `white`, `yellow`, `silver`.
- Hex: `4169E1`, `#FF8800`, etc.

### Resolución de `fieldname`

El valor del campo se obtiene de la fila de datos y admite varias formas:

| Forma | Ejemplo | Resuelve a |
|-------|---------|-----------|
| Campo directo | `amount` | `$row->amount` |
| Propiedad de objeto | `company.nombre` | `$row->company->nombre` |
| Elemento de array | `datos[clave]` | `$row->datos['clave']` |
| Llamada a método | `getTotal()` | `$row->getTotal()` |
| Método con parámetros | `getTotal('a','b')` | `$row->getTotal(['a','b'])` |


## `label` — texto (`WidgetLabel`)

El widget de texto general. Muestra un literal (`value`) o el valor de un campo (`fieldname`).

```xml
<widget type="label" value="report-test" translate="true" align="center" bold="true" size="18" color="black" />
<widget type="label" fieldname="name" italic="true" />
```

Atributos (además de los comunes):

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `align` | `left` | Alineación: `left`, `center`, `right`. |
| `size` | `10` | Tamaño de fuente. |
| `bold` | `false` | Negrita. |
| `italic` | `false` | Cursiva. |
| `underline` | `false` | Subrayado. |
| `translate` | `false` | Si es `true`, traduce el `value` con el idioma del usuario (útil para etiquetas fijas). |
| `bgcolor` | — | Color de fondo de la celda (mismo formato que `color`). |
| `prewrap` | `false` | Si es `true`, preserva los saltos de línea (`\n`) en la salida **HTML** (`white-space: pre-line`), de modo que un único valor multilínea se apile en pantalla. El PDF lo ignora (ya ajusta multilínea por sí mismo). |

Notas:
- El texto admite **multilínea**: se ajusta al `width` de la columna y salta de línea respetando la altura disponible. En HTML, para que un valor con `\n` se vea apilado, añade `prewrap="true"`.
- Las marcas de estilo se aplican como etiquetas `<b>`, `<i>`, `<u>` sobre el texto ya escapado.
- El atributo `bgcolor` también aplica en la salida HTML, generando un `background-color` inline sobre la celda.


## `default` — textos automáticos del sistema (`WidgetDefault`)

Muestra datos "de contexto" que no vienen de la fila, sino del entorno (empresa, usuario, fecha, página). Hereda de `label` pero **fuerza `translate=false`**. El valor se elige por el prefijo del `fieldname`:

```xml
<widget type="default" fieldname="company.nombre" size="12" color="white" />
<widget type="default" fieldname="user.nick" />
<widget type="default" fieldname="date" />
<widget type="default" fieldname="time" />
<widget type="default" fieldname="page" />
```

| `fieldname` | Muestra |
|-------------|---------|
| `company.<campo>` | Campo de la empresa (p. ej. `company.nombre`, `company.cifnif`). Si el nombre está vacío, muestra "todas las empresas". |
| `user.<campo>` | Campo del usuario logueado (p. ej. `user.nick`, `user.email`). |
| `date` | Fecha actual (`d-m-Y`). |
| `time` | Hora actual (`H:i:s`). |
| `page` | Número de página actual. |
| `<clave>.<campo>` | Cualquier dato extra pasado al construir la plantilla en el array `additional`. |
| `<clave>.<metodo>()` | Resultado de invocar un **método sin argumentos** sobre el dato extra `additional['<clave>']` (p. ej. `filters.reportFiltersText()`). Útil para que el objeto adicional calcule su propio texto. |

> El número de página se gestiona automáticamente: el motor lo incrementa al crear páginas nuevas, así que un `default` con `fieldname="page"` en la cabecera o el pie numera el documento sin esfuerzo.


## `number` — valores numéricos (`WidgetNumber`)

Para importes y cantidades con formato. Hereda de `label` (alineado a la derecha por defecto) y añade formato numérico, divisa, iconos y color para negativos.

```xml
<widget type="number" fieldname="amount" decimal="2" align="right" />
<widget type="number" fieldname="total" currency="coddivisa" negative="red" />
```

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `align` | `right` | Alineación (sobrescribe el `left` de `label`). |
| `decimal` | config. `default.decimals` (2) | Nº de decimales. |
| `printempty` | `true` | Si es `false`, no imprime nada cuando el valor está vacío. |
| `currency` | — | Nombre del **campo** del que se obtiene el código de divisa; activa el formato monetario con símbolo. |
| `licon` | — | Texto/símbolo a la **izquierda** del valor. |
| `ricon` | — | Texto/símbolo a la **derecha** del valor. |
| `negative` | = `color` | Color cuando el valor es negativo. |

Notas:
- Los separadores de miles y decimales se toman de la configuración del sistema (`default.thousands_separator`, `default.decimal_separator`).
- Si se indica `currency` y el dato tiene divisa, el símbolo se coloca a izquierda o derecha según `default.currency_position`.
- El atributo `negative` también aplica en la salida HTML: el valor se colorea con el color indicado cuando es negativo.


## `calculated` — totales acumulados (`WidgetCalculated`)

Acumula valores a lo largo del detalle y muestra el resultado, normalmente en el **pie del grupo**. Hereda de `number`, así que admite también su formato (decimales, divisa, color…).

```xml
<widget type="calculated" operator="sum"   fieldname="amount" align="right" bold="true" />
<widget type="calculated" operator="count" fieldname="code" />
<widget type="calculated" operator="max"   fieldname="amount" />
```

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `operator` | `sum` | Operación de agregación. |
| (+ atributos de `number`) | | Formato del resultado. |

Operadores disponibles:

| `operator` | Resultado |
|------------|-----------|
| `sum` | Suma de los valores del campo. |
| `count` | Recuento de filas. |
| `avg` | Media (promedio incremental). |
| `min` | Valor mínimo. |
| `max` | Valor máximo. |

Cómo funciona:
- El motor llama al `process()` del widget por cada fila del detalle, acumulando el total.
- Al pintar el pie, muestra el acumulado.
- En informes con **ruptura de subgrupo**, los totales del subgrupo se **reinician** (`reset()`) tras pintar cada pie de subgrupo, de modo que cada bloque tiene sus propios subtotales. Ver [04 - Grupos y rupturas](04-grupos-y-rupturas.md).


## `line` — líneas y rectángulos (`WidgetLine`)

Dibuja una línea horizontal separadora o, si tiene altura, un rectángulo (con relleno y/o borde).

```xml
<!-- línea separadora -->
<column posx="10" posy="1" width="550" height="1">
    <widget type="line" />
</column>

<!-- rectángulo de fondo -->
<column posx="10" posy="1" width="550" height="20">
    <widget type="line" border="1" height="20" bgcolor="silver" color="black" />
</column>
```

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `border` | `1` | Grosor del trazo/borde. `0` = sin borde. |
| `height` | `0` | Si supera el doble de `border`, se dibuja un **rectángulo** de esa altura; si no, una **línea**. |
| `color` | `black` | Color del trazo/borde. |
| `bgcolor` | — | Color de relleno (solo aplica al rectángulo). |


## `image` — imagen desde archivo (`WidgetImage`)

Inserta una imagen (JPG, PNG, GIF) cuya ruta se indica en `value` (o se resuelve por `fieldname`).

```xml
<widget type="image" value="/ruta/al/logo.png" resize="true" align="center" />
```

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `align` | `center` | Alineación de la imagen. |
| `angle` | `0` | Rotación en grados (0–360). |
| `padding` | `5` | Margen interior. |
| `resize` | `false` | Si es `true`, genera una **miniatura** ajustada al `width`/`height` de la columna (se cachea en `MyFiles/Tmp/Thumbnails/`). |

Las dimensiones de dibujo las toma del `width`/`height` de la columna. Formatos soportados: JPEG, PNG y GIF.


## `imageproduct` — imagen de un producto (`WidgetImageproduct`)

Variante de `image` que, a partir de la **referencia** de un producto (en `value`/`fieldname`), localiza su imagen asociada y la pinta. Útil para catálogos o fichas con foto del artículo.

```xml
<widget type="imageproduct" fieldname="referencia" resize="true" />
```

Hereda todos los atributos de `image`. Internamente:
- Busca la **variante** por su `referencia`.
- Obtiene la `ProductoImagen` asociada (prioriza la imagen específica de esa referencia y, si no, la del producto).
- Si no encuentra archivo, no pinta nada (falla en silencio).


## Crear un widget nuevo

Como la clase se resuelve dinámicamente (`Widget` + `type`), añadir un tipo propio es sencillo:

1. Crea `Lib/WidgetReport/WidgetMitipo.php` extendiendo `WidgetItem` (o un widget existente que te sirva de base).
2. Implementa el método `render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height)`.
3. Úsalo en el XML con `type="mitipo"`.

Si el widget participa en cálculos del pie, implementa además `process($data)` (acumular) y `reset()` (reiniciar en rupturas), como hace `WidgetCalculated`.
