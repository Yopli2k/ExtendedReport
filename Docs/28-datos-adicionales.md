Todas las plantillas de render (`PDFTemplate`, `CSVTemplate`, `HtmlTemplate`) aceptan un **array de datos adicionales** en su constructor. Estos datos son objetos o valores de contexto que no provienen del dataset principal (no son filas del informe), sino del entorno de la llamada: filtros activos, fechas del rango, parámetros de configuración, etc.

Una vez pasados, son accesibles desde el XML mediante el widget `default`.


## 🔧 Cómo se pasan

El tercer parámetro del constructor de cualquier plantilla es el array `$additional`. Cada clave del array es el nombre con el que accedes a ese dato desde el XML.

```php
$filtros = new MiObjetoFiltros();
$filtros->fechaDesde = '2025-01-01';
$filtros->fechaHasta = '2025-12-31';

$template = new PDFTemplate($this->user, $this->empresa, [
    'filtros' => $filtros,
    'ejercicio' => '2025',
]);
```

Lo mismo aplica para `CSVTemplate`, `HtmlTemplate` y `PDFReport`:

```php
$report = new PDFReport($this->response, ['filtros' => $filtros]);
```


## 🔑 Cómo se accede desde el XML

Se usa el widget `default` con el nombre de la clave seguido de `.` y la propiedad o método del objeto:

### Propiedad directa

```xml
<widget type="default" fieldname="filtros.fechaDesde" />
<widget type="default" fieldname="ejercicio" />
```

> Si la clave es un valor escalar (string, número) en lugar de un objeto, no uses `.` después del nombre: usa el nombre de la clave directamente, aunque el prefijo `default` lo resuelve como `additional['ejercicio']`.

### Llamada a método sin parámetros

```xml
<widget type="default" fieldname="filtros.resumenFiltros()" />
```

### Llamada a método con parámetros

```xml
<widget type="default" fieldname="filtros.resumen('corto')" />
```

Los parámetros se pasan al método como un **array**. El método debe recibirlos así:

```php
class MiObjetoFiltros
{
    public function resumen(array $params): string
    {
        $modo = $params[0] ?? 'largo';
        return $modo === 'corto' ? $this->resumenCorto() : $this->resumenLargo();
    }
}
```


## 📋 Ejemplo práctico

Mostrar el rango de fechas del informe en la cabecera:

```php
// controlador
$filtros = new \stdClass();
$filtros->desde = '01/01/2025';
$filtros->hasta = '31/12/2025';

$template = new PDFTemplate($this->user, $this->empresa, ['filtros' => $filtros]);
```

```xml
<!-- XML -->
<column posx="20" posy="45" width="200" area="meta">
    <widget type="default" fieldname="filtros.desde" size="10" />
</column>
<column posx="230" posy="45" width="200" area="meta">
    <widget type="default" fieldname="filtros.hasta" size="10" />
</column>
```

Mostrar un resumen de filtros como texto multilínea (solo en HTML):

```xml
<column posx="20" posy="60" width="540" area="meta" hideonpdf="true">
    <widget type="default" fieldname="filtros.resumenFiltros()" prewrap="true" />
</column>
```
