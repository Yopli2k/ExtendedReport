Un **widget** es el contenido que se dibuja dentro de una `<column>`. El atributo **`type`** determina qué clase se instancia: el motor concatena `Widget` + el `type` con la primera letra en mayúscula. Por ejemplo, `type="label"` instancia `WidgetLabel`, `type="number"` instancia `WidgetNumber`, etc.

Todas las clases de widget viven en `Lib/WidgetReport/` del plugin.


## 🗂️ Tipos de widget disponibles

- **`label`**: texto fijo o variable. Es el más utilizado. Ver **[11 - Widget label](11-widget-label.md)**.
- **`default`**: datos del sistema (empresa, usuario, fecha, hora, página). Ver **[12 - Widget default](12-widget-default.md)**.
- **`number`**: valores numéricos con formato (decimales, separadores, divisa, iconos). Ver **[13 - Widget number](13-widget-number.md)**.
- **`calculated`**: totales acumulados en el pie (`sum`, `count`, `avg`, `min`, `max`). Ver **[14 - Widget calculated](14-widget-calculated.md)**.
- **`line`**: líneas separadoras y rectángulos. Ver **[15 - Widget line](15-widget-line.md)**.
- **`image`**: imagen desde ruta de archivo. Ver **[16 - Widget image](16-widget-image.md)**.
- **`imageproduct`**: imagen de un producto a partir de su referencia. Ver **[16 - Widget image](16-widget-image.md)**.


## 🔧 Atributos comunes a todos los widgets

- **`type`** (obligatorio): tipo de widget.
- **`value`**: valor o texto literal a mostrar. Si se informa junto a `fieldname`, `fieldname` tiene prioridad.
- **`fieldname`**: nombre del campo del dato cuyo valor se muestra. Ver resolución de `fieldname` más abajo.
- **`color`**: color del texto o trazo. Por defecto `black`. Ver lista de colores más abajo.
- **`class`**: clases CSS adicionales para la salida **HTML** únicamente. Permite usar utilidades de Bootstrap (por ejemplo `class="h5"` o `class="text-muted"`). El PDF ignora este atributo.
- **`title`**: título de la tarjeta resumen cuando la columna usa `area="cards"`. Solo HTML. Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)**.
- **`cardcolor`**: color contextual de Bootstrap para la tarjeta resumen. Solo HTML. Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)**.


## 🎨 Colores disponibles

Puedes indicar el color por nombre o por valor hexadecimal:

Por nombre:
- `black` (negro, valor por defecto)
- `blue` (azul)
- `green` (verde)
- `orange` (naranja)
- `red` (rojo)
- `white` (blanco)
- `yellow` (amarillo)
- `silver` (gris claro)

Por valor hexadecimal (con o sin `#`):
- `4169E1` — azul royal
- `#FF8800` — naranja
- `CCCCCC` — gris neutro

Ejemplo:
```xml
<widget type="label" value="Título" color="4169E1" bold="true" />
```


## 🔍 Resolución de `fieldname`

El atributo `fieldname` acepta varias formas para acceder al valor de la fila actual:

- **Campo directo**: `fieldname="importe"` → `$fila->importe`
- **Propiedad de objeto**: `fieldname="empresa.nombre"` → `$fila->empresa->nombre`
- **Elemento de array**: `fieldname="totales[0]"` → `$fila->totales[0]`
- **Llamada a método sin parámetros**: `fieldname="getTotal()"` → `$fila->getTotal()`
- **Llamada a método con parámetros**: `fieldname="getTotal('eur','2')"` → `$fila->getTotal(['eur', '2'])`

> En los métodos con parámetros, estos se pasan al método como un array, no como argumentos individuales. Asegúrate de que tu método los recibe como `array $params`.
