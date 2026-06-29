El método `setRenderCfgValue()` permite ajustar el comportamiento del motor de render sin tocar el XML ni el modelo. Se llama sobre la instancia de la plantilla después de `loadTemplate()` y antes de `render()`.

```php
$template->setRenderCfgValue('clave', $valor);
```


## ⚙️ Opciones disponibles en PDFTemplate

### pageBreakOnRupture

Controla si cada ruptura de subgrupo fuerza un salto de página.

- Tipo: `bool`
- Por defecto: `false`

Con `false` (comportamiento por defecto), los subgrupos continúan en la misma página si hay espacio. Con `true`, cada subgrupo nuevo empieza siempre en una página nueva.

```php
$template = new PDFTemplate($this->user, $this->empresa);
$template->loadTemplate('MiInforme');
$template->setRenderCfgValue('pageBreakOnRupture', true);
$template->addDataset('main', $this->model);
$pdf = $template->render();
```

Útil para informes donde cada categoría debe quedar aislada en su propia hoja (por ejemplo, fichas de producto, presupuestos por cliente, etc.).


## 🔧 Cómo funciona internamente

`setRenderCfgValue()` almacena el par clave/valor en el objeto `ReportDefaultData`. El motor de render consulta estos valores durante el proceso de generación.

Las claves de configuración por defecto para `PDFTemplate` se declaran en el método `getDefaultRenderCfg()`:

```php
protected function getDefaultRenderCfg(): array
{
    return array_merge(parent::getDefaultRenderCfg(), [
        'pageBreakOnRupture' => false,
    ]);
}
```


## ➕ Añadir nuevas opciones en una subclase

Si extiendes `PDFTemplate` en tu plugin y necesitas añadir tu propia configuración de render, sobreescribe `getDefaultRenderCfg()`:

```php
class MiPDFTemplate extends PDFTemplate
{
    protected function getDefaultRenderCfg(): array
    {
        return array_merge(parent::getDefaultRenderCfg(), [
            'mostrarFirma'    => false,
            'mostrarSello'    => false,
        ]);
    }

    protected function renderFooter(GroupItem $group, float &$position, ?object $data = null, bool $second = false): void
    {
        parent::renderFooter($group, $position, $data, $second);

        if ($this->defaultData->getRenderCfg('mostrarFirma', false)) {
            // dibuja la zona de firma
        }
    }
}
```

Y en el controlador:

```php
$template = new MiPDFTemplate($this->user, $this->empresa);
$template->loadTemplate('MiInforme');
$template->setRenderCfgValue('mostrarFirma', true);
```
