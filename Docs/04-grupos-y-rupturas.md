# ExtendedReport — Grupos y rupturas

Este documento explica cómo se organizan las bandas en **grupos**, cómo funcionan las **rupturas de subgrupo** (subtotales por categoría), y cómo el motor gestiona los **saltos de página**. La estructura básica del XML está en [02 - Estructura del XML](02-estructura-xml.md) y los totales en el widget `calculated` en [03 - Widgets](03-widgets.md).


## Qué es un grupo

Un `<group>` agrupa las tres bandas de un informe: `header`, `detail` y `footer`. El atributo **`name`** lo identifica y sirve para **emparejarlo con su dataset**:

```php
$template->addDataset('main', $modelo);   // 'main' = name del grupo
```

Cuando el motor renderiza un grupo, busca el dataset cuyo nombre coincide con el `name` del grupo; si no lo encuentra, usa el dataset llamado `main` como respaldo. Por eso, en informes de un solo origen de datos, lo habitual es un único `<group name="main">`.

Un informe puede tener **varios grupos** a primer nivel (`<group name="ventas">`, `<group name="compras">`…), cada uno con su propio dataset, y se renderizan en orden.


## Ciclo de render de un grupo

Para cada grupo, el motor (`PDFTemplate::render`) ejecuta en este orden:

```
renderHeader(group)     → pinta la cabecera
renderDetail(group)     → recorre las filas del dataset
renderFooter(group)     → pinta el pie (totales)
```

Dentro de `renderDetail`, por **cada fila** del dataset:

1. Comprueba si hay **ruptura** (cambio en el campo `fieldname` del detalle).
2. Calcula el **espacio restante** en la página; si no cabe la fila, hace **salto de página** (pie + página nueva + cabecera).
3. Pinta la fila de detalle.
4. Acumula las **columnas calculadas** (`sum`, `count`, etc.).


## Subgrupos: `group` anidado dentro del detalle

En lugar de una banda `<detail>`, un grupo puede contener **otro `<group>`** en su lugar. Eso crea un **subgrupo**: una estructura cabecera → detalle → pie que se repite por cada bloque de datos con el mismo valor de ruptura.

```xml
<group name="main">
    <!-- cabecera general del informe -->
    <header height="60"> ... </header>

    <!-- subgrupo: se repite por cada "categoría" -->
    <group name="categoria">
        <header height="25">
            <column posx="20" posy="5" width="300">
                <widget type="label" fieldname="categoria" bold="true" />
            </column>
        </header>

        <detail height="20" fieldname="categoria">
            <column posx="30" width="250">
                <widget type="label" fieldname="name" />
            </column>
            <column posx="460" width="80">
                <widget type="number" fieldname="amount" align="right" />
            </column>
        </detail>

        <footer height="25">
            <column posx="460" posy="5" width="80">
                <widget type="calculated" operator="sum" fieldname="amount" align="right" bold="true" />
            </column>
        </footer>
    </group>

    <!-- pie general del informe -->
    <footer height="30">
        <column posx="460" posy="5" width="80">
            <widget type="calculated" operator="sum" fieldname="amount" align="right" bold="true" />
        </column>
    </footer>
</group>
```

En este ejemplo:
- El **grupo exterior** (`main`) aporta la cabecera y el pie generales (total global).
- El **subgrupo** (`categoria`) aporta una cabecera por categoría, las líneas de esa categoría y un **subtotal** por categoría.

La banda de detalle real siempre es la del nivel más interno: `getDetail()` desciende por los subgrupos hasta encontrar la banda `detail`.


## La ruptura: atributo `fieldname` en `<detail>`

La clave de los subtotales es el atributo **`fieldname`** de la banda `<detail>`. Indica el **campo de ruptura**: el motor compara su valor entre la fila actual y la anterior, y cuando **cambia** produce una ruptura.

```xml
<detail height="20" fieldname="categoria"> ... </detail>
```

Qué ocurre en una ruptura (cuando `categoria` cambia respecto a la fila previa):

1. Se pinta el **pie del subgrupo** con los totales acumulados del bloque que termina.
2. Se **reinician** las columnas calculadas del subgrupo (`reset()`), para que el siguiente bloque empiece sus subtotales desde cero.
3. Si está activo el salto por ruptura, se fuerza una **página nueva** (ver más abajo).
4. Se pinta la **cabecera del subgrupo** para el nuevo valor.

> **Importante:** la ruptura detecta *cambios* entre filas consecutivas. Para que los subtotales sean correctos, **el dataset debe venir ordenado por el campo de ruptura**. ExtendedReport no ordena los datos por ti: ese orden lo decides en el `loadData()` de tu `ModelReport`.

Métodos implicados (en `BandDetail`):
- `hasFieldRupture($row, $update)` — devuelve `true` si el valor del campo ha cambiado respecto al último visto.
- `hasFieldValue($row)` — devuelve `true` si el campo tiene valor (se usa para decidir si pintar la cabecera del subgrupo).


## Saltos de página

El motor gestiona los saltos automáticamente para que las bandas no se corten:

- Antes de pintar cada fila de detalle calcula el **espacio restante** en la página (alto de página menos la posición actual menos la altura reservada del pie). Si la fila (más, en su caso, la cabecera de subgrupo que la acompaña) **no cabe**, ejecuta un salto:
  1. Pinta el **pie** de la página actual (versión `second` si existe).
  2. Crea una **página nueva** e incrementa el contador de página.
  3. Repinta la **cabecera** (versión `second` si existe) en la página nueva.
- El número de página avanza solo, de modo que un widget `default` con `fieldname="page"` queda siempre actualizado.

### Bandas `main` vs `second`

Las bandas `header` y `footer` pueden definirse con `type="second"` además de la `main`. Permite que la **primera página** muestre una cabecera/pie distintos de los de las **páginas siguientes** (por ejemplo, cabecera completa con logo en la página 1 y cabecera reducida en el resto). Si no se define la versión `second`, se reutiliza la `main`.

### Salto de página en cada ruptura

Por defecto, una ruptura de subgrupo **no** fuerza salto de página: el siguiente bloque continúa en la misma página si hay espacio. Este comportamiento se controla con la configuración de render `pageBreakOnRupture`, que en `PDFTemplate` viene **desactivada** (`false`).

Para activarlo desde el código y que **cada subgrupo empiece en una página nueva**:

```php
$template->setRenderCfgValue('pageBreakOnRupture', true);
```


## Resumen del comportamiento

| Elemento | Cuándo se pinta |
|----------|-----------------|
| `header` del grupo | Al inicio del grupo y al inicio de cada página. |
| `header` del subgrupo | Cada vez que cambia el campo de ruptura (nuevo bloque). |
| `detail` | Una vez por cada fila del dataset. |
| `footer` del subgrupo | Al cerrar cada bloque de ruptura (subtotales). |
| `footer` del grupo | Al final del grupo y al pie de cada página (totales globales). |

| Para conseguir… | Necesitas… |
|-----------------|-----------|
| Subtotales por categoría | `group` anidado + `fieldname` en su `detail` + datos ordenados por ese campo. |
| Total global | `footer` en el grupo exterior con `calculated`. |
| Cada categoría en su página | `setRenderCfgValue('pageBreakOnRupture', true)`. |
| Cabecera/pie distintos en página 1 y resto | Definir bandas `type="second"`. |
| Numeración de páginas | `widget type="default" fieldname="page"`. |
