Para generar un PDF con ExtendedReport dispones de dos clases: **`PDFTemplate`** para control total y **`PDFReport`** como atajo para el caso habitual en un controlador.


## 🖨️ PDFTemplate — control total

`PDFTemplate` es la clase base del motor PDF. Te da control completo sobre cada paso.

```php
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFTemplate;

// 1. instanciar con el usuario y la empresa actuales
$template = new PDFTemplate($this->user, $this->empresa);

// 2. cargar el diseño XML
if (false === $template->loadTemplate('MiInforme')) {
    return; // el XML no se encontró en Dinamic/XMLView/Report/
}

// 3. cargar los datos y asociarlos al dataset
$this->model->loadData();
$template->addDataset('main', $this->model);

// 4. generar el PDF
$pdf = $template->render();

// 5. enviar la respuesta
$this->setTemplate(false);
$this->response->headers->set('Content-type', 'application/pdf');
$this->response->headers->set('Content-Disposition', 'inline;filename=mi-informe.pdf');
$this->response->setContent($pdf);
```

Si el informe tiene varios grupos con datasets distintos:
```php
$template->addDataset('ventas', $modelVentas);
$template->addDataset('compras', $modelCompras);
```

Si quieres pasar datos adicionales de contexto (filtros, fechas, etc.):
```php
$template = new PDFTemplate($this->user, $this->empresa, [
    'filtros' => $miFiltroObjeto,
]);
```
Ver **[28 - Datos adicionales](28-datos-adicionales.md)**.


## ⚡ PDFReport — atajo para controladores

`PDFReport` envuelve todo el flujo anterior en dos llamadas. Obtiene el usuario y la empresa directamente de la sesión, por lo que necesitas menos código.

```php
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFReport;

$report = new PDFReport($this->response);
if ($report->load($this->model, 'MiInforme')) {
    $report->show('mi-informe'); // envía el PDF y finaliza la respuesta
}
```

El método `load()` llama internamente a `$model->loadData()`, `loadTemplate()` y `addDataset()`. El método `show()` configura los headers y envía el contenido.

Si el dataset no es `main`:
```php
$report->load($this->model, 'MiInforme', 'ventas');
```

Si necesitas datos adicionales:
```php
$report = new PDFReport($this->response, ['filtros' => $miFiltroObjeto]);
```

> `PDFReport` solo cubre el caso de un único modelo con un único grupo. Para varios datasets o mayor control, usa `PDFTemplate` directamente.


## ⚙️ Configuración de render

`PDFTemplate` admite configuración especial mediante `setRenderCfgValue()`. Debe llamarse después de `loadTemplate()` y antes de `render()`.

```php
// hacer que cada subgrupo empiece en una página nueva
$template->setRenderCfgValue('pageBreakOnRupture', true);
```

Ver todas las opciones disponibles en **[32 - Configuración de render](32-configuracion-render.md)**.


## 📐 Calibrar el diseño

Si estás ajustando las posiciones `posx`/`posy` de un informe nuevo, el plugin incluye dos plantillas de calibración:

- `ColumnTestPortrait` — regla de coordenadas en A4 vertical.
- `ColumnTestLandscape` — regla de coordenadas en A4 horizontal.

```php
$template = new PDFTemplate($this->user, $this->empresa);
$template->loadTemplate('ColumnTestPortrait');
$template->addDataset('main', $this->model);
$pdf = $template->render();
```
