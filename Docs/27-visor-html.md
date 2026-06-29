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


## 📐 Cómo se construye la cuadrícula

El motor toma las posiciones `posx` de las columnas del `detail` y las convierte en columnas de la cuadrícula HTML. Las columnas con `posx` muy cercanos (diferencia menor a 8 puntos) se fusionan en una sola columna de tabla. Las demás bandas (cabecera, pie) se ajustan a esa misma cuadrícula, de modo que todos los valores quedan alineados verticalmente.

Si dos columnas que deberían estar en posiciones distintas de la tabla se fusionan, separa sus `posx` más de 8 puntos.


## 📌 Ejemplo en el controlador de prueba

El controlador `ReportTest` del plugin muestra tres variantes HTML de ejemplo:

- Acción `html-test`: informe simple con cabecera `meta` y tarjetas `cards`.
- Acción `html-grouped-test`: informe con ruptura de subgrupos.
- Acción `html-multiline-test`: informe multilinea con `subrows="3"`.

Es la referencia más rápida para ver cómo quedan los distintos patrones en pantalla.
