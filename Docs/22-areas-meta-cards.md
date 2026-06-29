El atributo **`area`** de la etiqueta `<column>` permite marcar determinadas columnas para que el **visor HTML** las trate de forma especial, sacándolas de la tabla principal y mostrándolas en bloques semánticos propios. En PDF y CSV estos atributos se ignoran por completo: la columna se pinta en su posición normal.

Existen dos valores posibles:
- **`area="meta"`**: para columnas de la cabecera con información contextual.
- **`area="cards"`**: para columnas del pie con totales que quieres destacar.


## 📋 area="meta" — Bloque de información superior

Cuando una columna del `<header>` lleva `area="meta"`, en el visor HTML se extrae de la tabla y se muestra en un **bloque de información** sobre la tabla de datos, sin bordes, con formato libre. Es el lugar ideal para mostrar la empresa, el usuario, las fechas del informe, los filtros aplicados, etc.

Las columnas sin `area="meta"` (o sin `area`) en el header se pintan como la fila de cabecera de la tabla (`<thead>`), con los títulos de columna.

```xml
<header height="150">
    <!-- bloque de información: van al área meta en HTML -->
    <column posx="20" posy="25" width="300" area="meta">
        <widget type="default" fieldname="company.nombre" bold="true" size="12" />
    </column>
    <column posx="20" posy="45" width="200" area="meta">
        <widget type="default" fieldname="user.nick" size="10" />
    </column>
    <column posx="450" posy="25" width="100" area="meta">
        <widget type="default" fieldname="date" size="10" />
    </column>

    <!-- títulos de columna: van al thead en HTML -->
    <column posx="20" posy="115" width="50">
        <widget type="label" value="code" translate="true" bold="true" size="13" />
    </column>
    <column posx="80" posy="115" width="350">
        <widget type="label" value="description" translate="true" bold="true" size="13" />
    </column>
    <column posx="460" posy="115" width="80">
        <widget type="label" value="total" translate="true" bold="true" align="right" size="13" />
    </column>
</header>
```


## 🃏 area="cards" — Tarjetas de resumen

Cuando una columna del `<footer>` lleva `area="cards"`, en el visor HTML el total calculado se muestra también como una **tarjeta de color pastel** encima de la tabla, a modo de indicadores clave del informe.

Para que la tarjeta tenga sentido, necesita un título y un color. Estos se indican en el `<widget>`:
- **`title`**: título de la tarjeta. Pasa por `Tools::trans`, así que admite una clave de traducción.
- **`cardcolor`**: color contextual de Bootstrap. Valores: `primary`, `secondary` (por defecto), `success`, `info`, `warning`, `danger`. Se pinta con la variante pastel (`bg-*-subtle`), compatible con el modo oscuro del tema.

```xml
<footer height="65">
    <!-- total con tarjeta de resumen en HTML -->
    <column posx="460" posy="30" width="80" area="cards">
        <widget type="calculated" operator="sum" fieldname="importe"
                align="right" bold="true"
                title="total" cardcolor="success" />
    </column>

    <!-- contador con tarjeta de resumen -->
    <column posx="85" posy="30" width="80" area="cards">
        <widget type="calculated" operator="count" fieldname="codigo"
                align="left" bold="true"
                title="count" cardcolor="info" />
    </column>
</footer>
```


## 👁️ Mostrar el total solo como tarjeta (sin fila en el pie)

Por defecto, una columna con `area="cards"` aparece **tanto en la fila del pie** (`<tfoot>`) **como en la tarjeta**. Si quieres que sea solo tarjeta y no ocupe espacio en el pie, combina `area="cards"` con `hideonview="true"`:

```xml
<column posx="460" posy="30" width="80" area="cards" hideonview="true">
    <widget type="calculated" operator="sum" fieldname="importe"
            title="total" cardcolor="success" />
</column>
```


## 📌 Resumen de comportamiento por formato

- En **PDF**: `area`, `title` y `cardcolor` se ignoran. La columna se pinta en su posición normal.
- En **CSV**: igual, se ignoran. Si la columna del footer tiene `fieldname`, su valor puede incluirse en el CSV del detalle si está en esa banda; los totales del footer no se exportan en CSV.
- En **HTML**:
    - `area="meta"` → la columna se mueve al bloque de metadatos sobre la tabla.
    - `area="cards"` → el total aparece como tarjeta pastel sobre la tabla y también en el `<tfoot>` (salvo que además tenga `hideonview="true"`).
