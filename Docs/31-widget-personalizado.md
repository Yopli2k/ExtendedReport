El motor de ExtendedReport resuelve el tipo de widget dinámicamente: dado `type="mitipo"` en el XML, busca la clase `WidgetMitipo` en el namespace de `WidgetReport`. Esto significa que añadir un nuevo tipo de widget solo requiere crear una clase PHP; no hay que tocar el parser ni ningún archivo del core del plugin.


## 🛠️ Pasos para crear un widget nuevo

### 1. Crear la clase

Crea el archivo en `Lib/WidgetReport/` de **tu plugin** (no en ExtendedReport). El nombre de la clase debe seguir el patrón `Widget` + el tipo con la primera letra en mayúscula.

```php
// TuPlugin/Lib/WidgetReport/WidgetRating.php
namespace FacturaScripts\Plugins\TuPlugin\Lib\WidgetReport;

use Cezpdf;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\WidgetItem;

class WidgetRating extends WidgetItem
{
    protected int $stars;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->stars = isset($data['stars']) ? (int) $data['stars'] : 5;
    }

    public function render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height): void
    {
        $rating = (int) $this->value;
        $text = str_repeat('★', $rating) . str_repeat('☆', $this->stars - $rating);

        $color = $this->color;
        $pdf->setColor($color['r'], $color['g'], $color['b']);
        $pdf->addText($posX, $posY, 10, $text, $width, 'left');
    }
}
```

### 2. Extender la clase adecuada

Elige la clase base según lo que necesites:

- **`WidgetItem`**: si el widget es completamente nuevo y no comparte lógica con los existentes.
- **`WidgetLabel`**: si necesitas estilos de texto (`bold`, `italic`, `align`, `bgcolor`…).
- **`WidgetNumber`**: si muestra un valor numérico con formato.
- **`WidgetCalculated`**: si acumula valores a lo largo del detalle.

### 3. Implementar `render()`

El método `render()` recibe el objeto PDF, las coordenadas y el espacio disponible. Es donde dibujas el contenido usando las primitivas de Cezpdf (ver documentación en `Docs/ROS PDF/`).

```php
public function render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height): void
{
    // dibuja lo que necesites usando $pdf->addText(), $pdf->filledRectangle(), etc.
}
```

### 4. Usarlo en el XML

```xml
<column posx="400" posy="5" width="80">
    <widget type="rating" fieldname="puntuacion" stars="5" color="orange" />
</column>
```


## 🔢 Si el widget participa en cálculos (totales)

Si quieres que tu widget acumule valores a lo largo del detalle y muestre un resultado en el pie, implementa también:

- **`process(object &$data)`**: llamado por cada fila del detalle. Actualiza `$this->value` con el acumulado.
- **`reset()`**: llamado al cerrar un subgrupo con ruptura. Reinicia `$this->value` al estado inicial.

```php
public function process(object &$data): void
{
    $newValue = (int) ($data->{$this->fieldname} ?? 0);
    $this->value = max((int) $this->value, $newValue); // ejemplo: máximo
}

public function reset(): void
{
    $this->value = 0;
}
```


## 🖥️ Si el widget necesita salida HTML

Implementa `toHtmlData()` para devolver la estructura que usa `HtmlTemplate`:

```php
public function toHtmlData(): array
{
    $data = parent::toHtmlData();
    $data['value'] = str_repeat('★', (int) $this->value)
                   . str_repeat('☆', $this->stars - (int) $this->value);
    return $data;
}
```
