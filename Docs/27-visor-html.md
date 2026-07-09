`HtmlTemplate` renderiza el mismo informe como una **tabla HTML responsive** en el navegador, usando Bootstrap. Reutiliza el mismo XML y el mismo modelo de datos que el PDF, pero interpreta el diseño de forma semántica en lugar de por coordenadas.


## 🔄 Cómo interpreta el XML

El motor HTML traduce las bandas del XML a elementos HTML de la siguiente forma:

- **`header`** (columnas sin `area`): se convierte en las cabeceras de columna de la tabla (`<thead>` con `<th>`).
- **`header`** (columnas con `area="meta"`): se extraen y se muestran en un bloque informativo sobre la tabla (empresa, usuario, fecha, filtros…). Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)**.
- **`detail`**: se convierte en las filas de datos de la tabla (`<tbody>` con `<td>`).
- **Cabeceras de subgrupo** (ruptura): aparecen como filas resaltadas (`<th class="table-active">`) dentro del cuerpo.
- **Pies de subgrupo** (subtotales): aparecen como filas resaltadas (`<td class="table-active">`) dentro del cuerpo.
- **`footer`**: se convierte en la fila de totales al pie de la tabla (`<tfoot>` en negrita).
- **`footer`** (columnas con `area="cards"`): aparecen también como tarjetas de resumen sobre la tabla. Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)**.

Los widgets `line` se descartan: la tabla HTML ya tiene sus propios bordes. Las columnas con `hideonview="true"` también se descartan.


## 🖥️ Uso en el controlador

```php
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\HtmlTemplate;

// dentro del método que gestiona la acción HTML:
$report = new HtmlTemplate($this->user, $this->empresa);
if (false === $report->loadTemplate('MiInforme')) {
    return;
}

$this->model->loadData();
$report->addDataset('main', $this->model);

// render() devuelve un string HTML
$this->reportHtml = $report->render();

// usar la vista del visor
$this->setTemplate('ReportHtmlViewer');
```

La propiedad `$reportHtml` debe ser **pública** en tu controlador para que Twig pueda acceder a ella.


## 📄 La vista ReportHtmlViewer

El plugin incluye la vista `View/ReportHtmlViewer.html.twig` lista para mostrar el HTML del informe. La activas con `$this->setTemplate('ReportHtmlViewer')`.

No existe ningún controlador `ReportHtmlViewer`: es simplemente una vista Twig que el controlador del informe carga cuando la acción es de tipo HTML.

Si prefieres incrustar el informe dentro de una vista propia, usa el bloque reutilizable `View/Block/ReportHtml.html.twig`:

```twig
{% include '@PluginExtendedReport/Block/ReportHtml.html.twig' %}
```


## 🖱️ Celdas clicables — detailclick

Las columnas del `detail` marcadas con `detailclick="true"` se vuelven clicables en el visor: al pulsarlas se abre un **modal con el desglose del valor** (la lista de documentos o líneas que suman ese importe).

```xml
<column posx="330" posy="0" width="55" detailclick="true">
    <widget type="number" fieldname="current_value[0]" align="right" />
</column>
```

El mecanismo tiene dos partes:

**1. El motor HTML** emite en cada celda marcada los atributos `data-code` (el campo `code` del registro de datos) y `data-field` (el `fieldname` del widget), junto con la clase `er-detailclick`. Si el registro no tiene campo `code`, la celda no se marca.

**2. El visor `ReportHtmlViewer`** incluye un modal genérico y el JavaScript que lo alimenta, pero solo se activa si el controlador define la propiedad pública `detailClickUrl` con la URL que responde al desglose:

```php
// en el controlador, antes de setTemplate('ReportHtmlViewer'):
$this->detailClickUrl = 'EditMiInforme?action=report-detail&code=' . $model->id;
```

Al hacer clic, el visor añade a esa URL los parámetros `detailcode` (el `data-code` de la celda) y `detailfield` (el `data-field`), y espera una respuesta **JSON** con este contrato:

```json
{
    "title": "Cliente ACME — Total 2026",
    "headers": [
        { "title": "Fecha", "class": "" },
        { "title": "Documento", "class": "" },
        { "title": "Neto", "class": "text-end" }
    ],
    "rows": [
        ["12-01-2026", "FAC-0001", "1.234,56"],
        ["03-02-2026", "FAC-0017", "890,00"]
    ],
    "total": ["", "", "2.124,56"],
    "note": "Mostrando 200 de 450 documentos"
}
```

- `title`: título del modal. Opcional (por defecto, *Detalle*).
- `headers`: cabeceras de columna, con clase CSS opcional que se aplica a toda la columna (p. ej. `text-end` para importes).
- `rows`: filas con los valores **ya formateados** por el servidor.
- `total`: fila de totales al pie. Opcional.
- `note`: texto informativo bajo la tabla (p. ej. aviso de truncado). Opcional.

La tabla del desglose se envuelve en su propio contenedor con scroll acotado (`max-height: 60vh; overflow-y: auto`) y cabecera fija (`position: sticky` en cada `<th>`), en vez de apoyarse en `modal-dialog-scrollable`/`modal-body`: al ser `.modal-body` un elemento flex dentro de `.modal-content`, no siempre se ajusta a un alto concreto, por lo que el scroll y el elemento sticky pueden terminar enganchados a un ancestro distinto (el modal completo o la página) y las filas acaban pintándose sobre la cabecera. El contenedor propio de la tabla evita esa ambigüedad. Aun así, es responsabilidad del servidor **limitar el número de filas** e indicarlo en `note`.


## 🖨️ Imprimir el PDF real — printUrl

Por defecto, el botón de imprimir del visor llama a `window.print()`, que imprime la propia página HTML tal cual la ve el navegador. Si prefieres que imprima el informe en su maquetación PDF real (la misma que generaría el controlador con la acción de imprimir), define la propiedad pública `printUrl` con la URL que responde con el PDF, antes de `setTemplate('ReportHtmlViewer')`:

```php
// en el controlador, antes de setTemplate('ReportHtmlViewer'):
$this->printUrl = $this->url() . '?action=print-test';
```

Si `printUrl` está definida, el botón abre esa URL en una pestaña nueva (el navegador muestra el PDF con su propio visor, desde el que se puede imprimir); si no, mantiene el comportamiento anterior de `window.print()`.

El controlador `ReportTest` ilustra esto en la acción `html-test`, enlazando con la acción `print-test` que ya genera el PDF con `PDFTemplate`.


## 📐 Cómo se construye la cuadrícula

El motor toma las posiciones `posx` de las columnas del `detail` y las convierte en columnas de la cuadrícula HTML. Las columnas con `posx` muy cercanos (diferencia menor a 8 puntos) se fusionan en una sola columna de tabla. Las demás bandas (cabecera, pie) se ajustan a esa misma cuadrícula, de modo que todos los valores quedan alineados verticalmente.

Si dos columnas que deberían estar en posiciones distintas de la tabla se fusionan, separa sus `posx` más de 8 puntos.


## 📌 Ejemplo en el controlador de prueba

El controlador `ReportTest` del plugin muestra tres variantes HTML de ejemplo:

- Acción `html-test`: informe simple con cabecera `meta` y tarjetas `cards`.
- Acción `html-grouped-test`: informe con ruptura de subgrupos.
- Acción `html-multiline-test`: informe multilinea con `subrows="3"`.

Es la referencia más rápida para ver cómo quedan los distintos patrones en pantalla.
