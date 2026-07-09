La etiqueta `<column>` define una **celda posicionada** dentro de una banda. Contiene exactamente un `<widget>` que determina qué se dibuja en ese espacio.

```xml
<column posx="20" posy="25" width="200" height="15">
    <widget type="label" value="Hola" />
</column>
```


## 📐 Atributos de posición y tamaño

- **`posx`** (por defecto `0`): posición horizontal desde el borde izquierdo de la página, en puntos.
- **`posy`** (por defecto `0`): posición vertical desde el inicio de la banda, hacia abajo, en puntos.
- **`width`** (por defecto `30`): ancho de la celda en puntos.
- **`height`** (por defecto `15`): alto de la celda en puntos.


## 🗺️ Cómo funciona el sistema de coordenadas

El origen de cada columna se calcula a partir de la posición actual de la banda:
- **`posx`**: horizontal, desde el borde izquierdo.
- **`posy`**: vertical dentro de la banda, de arriba hacia abajo. El motor lo convierte internamente para Cezpdf, que trabaja desde la esquina inferior izquierda. No necesitas hacer ninguna conversión manual.

Dentro de una misma banda, columnas con mayor `posy` quedan más abajo.

> **Calibrar coordenadas**: el plugin incluye las plantillas `ColumnTestPortrait` y `ColumnTestLandscape` que imprimen una regla de ejes sobre el papel. Úsalas para calibrar tus `posx`/`posy` antes de maquetar un informe nuevo. Las puedes lanzar desde el controlador de prueba `ReportTest` del propio plugin (menú *Admin*).


## 🏷️ Atributos de área y visibilidad

Estos atributos solo afectan a la salida **HTML en pantalla**; en PDF y CSV se ignoran.

- **`area`**: agrupa la columna en una zona semántica especial del visor HTML:
    - `"meta"` (solo en `header`): la columna se muestra en el bloque de información superior (empresa, fecha, filtros…), fuera de la tabla de datos. Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)**.
    - `"cards"` (solo en `footer`): el total de la columna se muestra también como una tarjeta de resumen sobre la tabla. Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)**.

- **`detailclick`**: si es `true`, la celda es **clicable en el visor HTML** y emite los atributos `data-code` (el campo `code` del registro) y `data-field` (el `fieldname` del widget) para que el visor pueda solicitar al servidor el desglose del valor. Ver **[27 - Visor HTML](27-visor-html.md)**.

- **`hideonview`**: si es `true`, la columna **no se muestra en el visor HTML**. Útil para elementos que solo tienen sentido en papel (número de página, títulos con coordenadas específicas de PDF, etc.).

- **`hideonpdf`**: si es `true`, la columna **no se pinta en el PDF**. Útil para columnas pensadas exclusivamente para la vista HTML.

Ver **[23 - Visibilidad por formato](23-visibilidad-formato.md)**.


## 📋 Ejemplos

Columna estándar de datos:
```xml
<column posx="80" posy="0" width="250">
    <widget type="label" fieldname="nombre" />
</column>
```

Columna de cabecera visible solo en PDF (número de página):
```xml
<column posx="500" posy="25" width="50" hideonview="true">
    <widget type="default" fieldname="page" />
</column>
```

Columna de metadatos visible solo en HTML (empresa):
```xml
<column posx="20" posy="25" width="300" area="meta">
    <widget type="default" fieldname="company.nombre" bold="true" />
</column>
```

Columna de total con tarjeta de resumen en HTML:
```xml
<column posx="460" posy="30" width="80" area="cards">
    <widget type="calculated" operator="sum" fieldname="importe"
            align="right" bold="true" title="total" cardcolor="success" />
</column>
```
