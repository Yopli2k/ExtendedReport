# ExtendedReport — Estructura del XML

Este documento describe en detalle el archivo de diseño XML de un informe: dónde se guarda, qué etiquetas admite y qué significa cada atributo. Es la referencia de maquetación; los widgets concretos se tratan en [03 - Widgets](03-widgets.md) y las agrupaciones en [04 - Grupos y rupturas](04-grupos-y-rupturas.md).


## Dónde se guarda

Los diseños se colocan en la carpeta del plugin que use ExtendedReport:

```
TuPlugin/XMLView/Report/MiInforme.xml
```

El motor los busca a través de **Dinamic** (`Dinamic/XMLView/Report/MiInforme.xml`), por lo que tras crear o modificar un XML hay que **reconstruir/desplegar** los plugins para que aparezca en `Dinamic`. En modo `FS_DEBUG`, si no encuentra el archivo en `Dinamic` lo busca también en `Core/XMLView/Report/`.

El nombre que se pasa a `loadTemplate('MiInforme')` es el del archivo **sin extensión**.


## Jerarquía general

Un informe tiene esta estructura de etiquetas:

```xml
<report>
    <config> ... </config>          <!-- 0 o 1 -->
    <group name="..."> ... </group>  <!-- 1 o varios -->
</report>
```

```
report
├── config            → configuración general (página, fuente, defaults)
└── group (1..n)      → cada grupo agrupa hasta 3 bandas
    ├── header        → banda de cabecera
    ├── detail        → banda de detalle (se repite por fila)   ó   group anidado
    └── footer        → banda de pie (totales)
        └── column (n)        → celda posicionada
            └── widget        → contenido a pintar
```

El parser (`ReportItemLoadEngine`) recorre los hijos de `<report>`: las etiquetas `group` se convierten en objetos `GroupItem` indexados por su atributo `name`, y la etiqueta `config` en un `ConfigItem`.


## La etiqueta `<config>`

Define la configuración global del informe. Es opcional; si falta, se aplican valores por defecto.

```xml
<config>
    <page type="A4" orientation="portrait" />
    <font type="Arial" size="12" />
    <default group="main" alter="other" />
</config>
```

### `<page>`

| Atributo | Valores | Por defecto | Descripción |
|----------|---------|-------------|-------------|
| `type` | `A4`, `Letter`, etc. (tamaños de Cezpdf) | `a4` | Tamaño de página. |
| `orientation` | `portrait`, `landscape` | `portrait` | Orientación de la página. |

El tamaño y la orientación determinan el ancho/alto útil (`pageWidth` / `pageHeight`) que se usa para calcular posiciones y saltos de página.

### `<font>`

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `type` | `Helvetica` | Tipo de letra. |
| `size` | `12` | Tamaño base de fuente. |

> **Nota:** actualmente la fuente queda **forzada a `Helvetica`** en el código (`ConfigItem::loadFontConfig`). Para usar otras tipografías habría que añadir los archivos `.ttf` correspondientes a la librería PDF. El `type` del XML se ignora de momento.

### `<default>`

| Atributo | Por defecto | Descripción |
|----------|-------------|-------------|
| `group` | `main` | Nombre del grupo principal por defecto. |
| `alter` | `other` | Identificador alternativo (reservado para uso interno/futuro). |


## La etiqueta `<group>`

Agrupa las bandas del informe. El atributo **`name`** es importante: identifica el grupo y se usa para **emparejarlo con su dataset** (los datos que se pasan con `addDataset('nombre', $modelo)`). Si no hay un dataset con ese nombre, se usa el dataset `main`.

```xml
<group name="main">
    <header> ... </header>
    <detail> ... </detail>
    <footer> ... </footer>
</group>
```

Dentro de un grupo puede haber:

- Una banda **`detail`** (las filas), **o** un **`group` anidado** en su lugar (subgrupos con ruptura — ver [04 - Grupos y rupturas](04-grupos-y-rupturas.md)).
- Hasta dos bandas **`header`** y dos **`footer`** distinguidas por su atributo `type` (`main` / `second`), para diferenciar la versión que se pinta en la primera página de la que se repite en las siguientes.


## Las bandas: `<header>`, `<detail>`, `<footer>`

Las tres comparten una base común (`BandItem`): contienen columnas y tienen una **altura** que el motor usa para reservar el espacio vertical y decidir los saltos de página.

| Atributo común | Descripción |
|----------------|-------------|
| `height` | Altura de la banda en puntos. Es la referencia que avanza la posición vertical al pintarla. |
| `type` | `main` (por defecto) o `second`. Permite tener una variante de cabecera/pie para páginas posteriores. |

### `<header>`

Se pinta al comienzo del grupo y al inicio de cada página nueva.

| Atributo extra | Descripción |
|----------------|-------------|
| `newpage` | Si es `true`, fuerza salto de página. |

### `<detail>`

Es la banda que **se repite una vez por cada fila** del dataset. Es la única que admite el campo de ruptura.

- **`fieldname`** — campo que provoca la **ruptura de subgrupo** cuando cambia su valor entre filas. Ver [04 - Grupos y rupturas](04-grupos-y-rupturas.md).
- **`subrows`** — número de subfilas que puede contener una fila de detalle en el visor HTML. Por defecto `1`. Cuando en el detalle hay columnas con distinto `posy` (varias filas apiladas), indica al motor HTML cuántas líneas de altura puede ocupar cada fila; con esto el alineamiento de las celdas apiladas queda correcto. En PDF se ignora (el PDF ya gestiona las posiciones por coordenadas).

### `<footer>`

Se pinta al final del grupo y al pie de cada página. Es donde van los totales (`widget type="calculated"`).

| Atributo extra | Descripción |
|----------------|-------------|
| `newpage` | Si es `true`, fuerza salto de página tras el pie. |
| `placebottom` | Si es `true`, ancla el pie a la parte baja de la página. |


## La etiqueta `<column>`

Cada columna es una **celda posicionada** dentro de la banda. Contiene exactamente un `widget`.

```xml
<column posx="20" posy="25" width="540" height="20">
    <widget type="label" value="Hola" />
</column>
```

- **`posx`** (por defecto `0`) — posición horizontal desde el borde izquierdo.
- **`posy`** (por defecto `0`) — posición vertical relativa al inicio de la banda (hacia abajo).
- **`width`** (por defecto `30`) — ancho de la celda.
- **`height`** (por defecto `15`) — alto de la celda.
- **`area`** — agrupa la columna en una zona semántica especial. El único valor activo es `"meta"`: las columnas marcadas con `area="meta"` en la cabecera se renderizan en el **bloque de información superior** del visor HTML (nombre del informe, empresa, fecha…) en lugar de en la tabla de datos. En PDF se ignora y la columna se pinta en su posición normal.
- **`hideonview`** — si es `true`, la columna **no se muestra en el visor HTML**. Útil para columnas que solo tienen sentido en papel (número de página, saltos de página decorativos…).
- **`hideonpdf`** — si es `true`, la columna **no se pinta en el PDF**. Útil para columnas pensadas exclusivamente para la vista HTML.

### Cómo se interpretan las coordenadas

- El origen de cada columna se calcula a partir de la posición actual de la banda: el motor toma la línea base y le **resta `posy`**, de modo que dentro de una banda los `posy` mayores quedan **más abajo**.
- Internamente Cezpdf trabaja con el origen en la **esquina inferior izquierda**, pero ExtendedReport hace la conversión por ti (`pagePosition`), así que tú maquetas pensando "de arriba hacia abajo" dentro de cada banda.
- Para **calibrar** los `posx`/`posy` de un diseño nuevo es muy útil el controlador de prueba `ReportTest`, que imprime una **regla de coordenadas** en vertical y horizontal (plantillas `ColumnTestPortrait` / `ColumnTestLandscape`).


## La etiqueta `<widget>`

Define el contenido que se dibuja dentro de la columna. El atributo **`type`** decide qué clase se instancia (`Widget` + `type` capitalizado, p. ej. `type="label"` → `WidgetLabel`).

```xml
<widget type="label" fieldname="name" align="left" bold="true" size="12" color="black" />
```

Los atributos disponibles dependen de cada tipo de widget. Consulta la referencia completa en [03 - Widgets](03-widgets.md). Los dos más habituales, comunes a casi todos:

- **`value`** — texto/valor literal.
- **`fieldname`** — nombre del campo del dato cuyo valor se quiere mostrar (admite `objeto.propiedad`, `array[clave]` y `metodo()`).


## Ejemplo completo mínimo

```xml
<?xml version="1.0" encoding="UTF-8"?>
<report>
    <config>
        <page type="A4" orientation="portrait" />
        <font type="Arial" size="12" />
        <default group="main" alter="other" />
    </config>

    <group name="main">
        <header height="40">
            <column posx="20" posy="10" width="300">
                <widget type="label" value="Listado" bold="true" size="16" />
            </column>
        </header>

        <detail height="20">
            <column posx="20" width="50">
                <widget type="label" fieldname="id" />
            </column>
            <column posx="80" width="250">
                <widget type="label" fieldname="name" />
            </column>
            <column posx="460" width="80">
                <widget type="number" fieldname="amount" align="right" />
            </column>
        </detail>

        <footer height="30">
            <column posx="10" posy="1" width="550" height="1">
                <widget type="line" />
            </column>
            <column posx="460" posy="10" width="80">
                <widget type="calculated" operator="sum" fieldname="amount" align="right" bold="true" />
            </column>
        </footer>
    </group>
</report>
```

Este diseño produce un PDF con un título, una fila por registro (`id`, `name`, `amount`) y un pie con una línea separadora y la suma de la columna `amount`.
