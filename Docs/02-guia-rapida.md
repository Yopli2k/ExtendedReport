Esta guía muestra el camino más corto para crear un informe PDF funcional con ExtendedReport. El ejemplo imprime un listado simple con id, nombre e importe, con un total al pie.


## Paso 1 — Crear el modelo de datos

Crea la clase que obtendrá las filas del informe. Debe extender `ModelReport` e implementar `loadData()`.

```php
// TuPlugin/Model/Report/MiListado.php
namespace FacturaScripts\Plugins\TuPlugin\Model\Report;

use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\ModelReport;

class MiListado extends ModelReport
{
    public function loadData(): void
    {
        $sql = 'SELECT id, name, amount FROM mitabla ORDER BY name';
        foreach (self::$dataBase->select($sql) as $row) {
            $item = new \stdClass();
            $item->id     = $row['id'];
            $item->name   = $row['name'];
            $item->amount = $row['amount'];
            $this->data[] = $item;
        }
    }
}
```


## Paso 2 — Crear el diseño XML

Crea el archivo de diseño en la carpeta `XMLView/Report/` de tu plugin.

```xml
<!-- TuPlugin/XMLView/Report/MiListado.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<report>
    <config>
        <page type="A4" orientation="portrait" />
        <font type="Arial" size="12" />
        <default group="main" />
    </config>

    <group name="main">

        <header height="40">
            <column posx="20" posy="10" width="300">
                <widget type="label" value="mi-listado" translate="true"
                        bold="true" size="16" />
            </column>
        </header>

        <detail height="20">
            <column posx="20" width="50">
                <widget type="label" fieldname="id" />
            </column>
            <column posx="80" width="300">
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
            <column posx="380" posy="15" width="70">
                <widget type="label" value="total" translate="true"
                        align="right" bold="true" />
            </column>
            <column posx="460" posy="15" width="80">
                <widget type="calculated" operator="sum" fieldname="amount"
                        align="right" bold="true" />
            </column>
        </footer>

    </group>
</report>
```


## Paso 3 — Generar el PDF en el controlador

```php
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFTemplate;
use FacturaScripts\Plugins\TuPlugin\Model\Report\MiListado;

// dentro del método que gestiona la acción de impresión:
$model = new MiListado();
$model->loadData();

$template = new PDFTemplate($this->user, $this->empresa);
if (false === $template->loadTemplate('MiListado')) {
    return; // el XML no se encontró
}

$template->addDataset('main', $model);
$pdf = $template->render();

$this->setTemplate(false);
$this->response->headers->set('Content-type', 'application/pdf');
$this->response->headers->set('Content-Disposition', 'inline;filename=mi-listado.pdf');
$this->response->setContent($pdf);
```


## Resultado

Con estos tres pasos obtienes:
- Una cabecera con el título traducido.
- Una fila por cada registro de la tabla, con id, nombre e importe.
- Un pie con una línea separadora y la suma del importe.
- Paginación automática: si los registros no caben en una página, el motor añade páginas nuevas repintando la cabecera.


## 💡 Atajo con PDFReport

Si no necesitas control fino sobre la respuesta HTTP, `PDFReport` reduce el código del controlador:

```php
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFReport;

$model = new MiListado();
$report = new PDFReport($this->response);
if ($report->load($model, 'MiListado')) {
    $report->show('mi-listado');
}
```

Consulta **[25 - Generar PDF](25-generar-pdf.md)** para más detalles sobre `PDFTemplate` y `PDFReport`.


## 📐 Calibrar coordenadas

Las posiciones `posx`/`posy` se miden en puntos sobre el papel. El plugin incluye dos plantillas de referencia que imprimen una regla de coordenadas:

- `ColumnTestPortrait` — para orientación vertical.
- `ColumnTestLandscape` — para orientación horizontal.

Puedes verlas en acción desde el controlador de ejemplo `ReportTest` del propio plugin (menú *Admin* mientras `showonmenu` sea `true`).
