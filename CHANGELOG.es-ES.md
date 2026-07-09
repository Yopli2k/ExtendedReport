# Changelog

Todos los cambios notables en este proyecto se documentarán en este archivo.

## [2.12] - 2026-07-09

### Nuevas funcionalidades y mejoras

- Nuevo desglose de celdas (`detailclick`) en el visor de informes HTML. Las celdas del detalle marcadas con `detailclick="true"` se vuelven clicables y abren un modal con el listado de registros que componen ese valor (por ejemplo, los documentos detrás de un total).
    - Nuevo atributo de columna `detailclick="true"` — el motor HTML emite `data-code` y `data-field` en la celda para que el visor pueda pedir el desglose al servidor.
    - Nuevo modal genérico (`View/Block/ReportDetailModal.html.twig`) con el JavaScript que solicita el desglose a la URL definida por el controlador y pinta la tabla de resultado (cabeceras, filas, total y nota opcional). Solo se activa si el controlador define la propiedad pública `detailClickUrl`.
    - Si el desglose solo tiene un registro, no se pinta la fila de totales, ya que coincidiría con esa única fila y sería redundante.
    - La cabecera de la tabla del visor y del modal de desglose pasa a usar el color `table-primary` en vez de `table-light`.

- Nuevo botón de impresión del PDF real desde el visor de informes HTML. Si el controlador define la propiedad pública `printUrl`, el botón "Imprimir" abre en una pestaña nueva el PDF generado por `PDFTemplate`, en lugar de imprimir la propia página HTML con `window.print()` del navegador.
    - Conectado en el controlador de ejemplo `ReportTest` (acción `html-test`) y en `EditStatisticController` del plugin InformesEstadisticos.

## [2.11] - 2026-06-29

### Nuevas funcionalidades y mejoras

- Nuevas tarjetas resumen (cabecera estadística) en el visor de informes HTML. Cualquier total del pie puede destacarse como una tarjeta de color pastel sobre la tabla, de modo que las cifras clave (totales, recuentos…) se ven de un vistazo sin bajar hasta el pie.
    - Nueva zona de columna `area="cards"`: marca un total del pie para mostrarlo además como tarjeta resumen. Es aditivo — el total sigue apareciendo en la fila del pie salvo que la columna lleve también `hideonview="true"` (entonces se muestra solo como tarjeta).
    - Nuevo atributo `title` en el widget — título de la tarjeta (traducido con `Tools::trans`), para que la cifra tenga siempre una etiqueta.
    - Nuevo atributo `cardcolor` en el widget — color contextual de Bootstrap de la tarjeta (`primary`, `secondary`, `success`, `info`, `warning`, `danger`), pintado con su variante pastel `bg-*-subtle`, que además respeta el modo oscuro del tema. Por defecto `secondary`.
    - Sin JavaScript: los totales del pie ya están acumulados cuando se genera el HTML (el pie se evalúa tras recorrer todas las filas), así que las tarjetas se pintan arriba con los valores finales en una única pasada en servidor.
    - `area`, `title` y `cardcolor` se ignoran en las salidas PDF y CSV.

## [2.10] - 2026-06-27

### Nuevas funcionalidades y mejoras

- Añadido visor de informes HTML en pantalla. El mismo diseño XML y modelo de datos que se usa para PDF/CSV puede ahora renderizarse como una tabla HTML responsive directamente en el navegador, sin descargar ningún archivo.
    - Nuevo motor `HtmlTemplate` que interpreta el XML de forma semántica: cabecera → `thead`, filas de detalle → `tbody`, pie → `tfoot`, cabeceras/pies de subgrupo → filas de sección y subtotales.
    - Nuevo controlador `ReportHtmlViewer` que abre el informe HTML en una nueva pestaña del navegador.
    - Nuevo bloque Twig reutilizable (`View/Block/ReportHtml.html.twig`) que pinta la tabla del informe y puede incrustarse en la vista de cualquier plugin.
    - Las clases CSS de Bootstrap, colores y fondos declarados en el XML se propagan a la salida HTML.

- Nuevos atributos de columna en el XML para controlar la salida HTML de forma precisa:
    - `area="meta"` — marca las columnas de la cabecera como metadatos (nombre de empresa, título del informe, fecha…). Se renderizan en un bloque de información sobre la tabla en lugar de como una fila de tabla. El PDF lo ignora.
    - `hideonview="true"` — oculta la columna en el visor HTML (útil para columnas de número de página o decorativas).
    - `hideonpdf="true"` — oculta la columna en el PDF (útil para columnas pensadas exclusivamente para la vista HTML).

- Nuevo atributo `subrows` en `<detail>` — declara cuántas subfilas apiladas puede contener una fila de detalle en la vista HTML, permitiendo el alineamiento vertical correcto de celdas que comparten la misma fila pero tienen distintos valores de `posy`. El PDF lo ignora.

- Nuevo atributo `class` en `<widget>` — permite añadir clases CSS adicionales (p. ej. utilidades de Bootstrap como `h2`, `fw-bold`, `text-end`) a la celda HTML. El PDF lo ignora.

- El alineamiento del pie y de los pies de subgrupo en la vista HTML se gestiona ahora mediante un algoritmo de asignación de columnas en dos pasadas: primero se colocan los widgets anclados en la columna de la rejilla cuyo `posx` coincide; después las etiquetas flotantes (como "Totales…") se ubican en el hueco libre más cercano. Esto produce filas de pie correctamente alineadas sin necesidad de añadir atributos extra en el XML.
