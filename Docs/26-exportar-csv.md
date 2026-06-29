`CSVTemplate` genera un archivo de texto separado por `;` con los valores del detalle del informe, reutilizando el mismo diseño XML del PDF. No hay que crear un exportador aparte.


## ⚙️ Qué exporta y qué no

El CSV solo exporta las columnas de la **banda `detail`** que tengan un `fieldname` definido. Las columnas sin `fieldname` (etiquetas fijas, líneas decorativas, imágenes) se omiten.

Las bandas `header` y `footer` **no se exportan**: el CSV no incluye cabeceras visuales ni totales calculados.

Formato de salida:
- Separador de campos: `;`
- Delimitador de texto: `"`
- Primera fila: nombres de los campos (`fieldname` de cada columna incluida).
- Filas siguientes: valores de cada registro.

Ejemplo de salida para un detalle con `fieldname="codigo"`, `fieldname="nombre"` y `fieldname="importe"`:
```
"codigo";"nombre";"importe"
"A001";"Artículo uno";"150.00"
"A002";"Artículo dos";"200.00"
```


## 🖥️ Uso en el controlador

```php
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\CSVTemplate;

$template = new CSVTemplate($this->user, $this->empresa);
if (false === $template->loadTemplate('MiInforme')) {
    return;
}

$this->model->loadData();
$template->addDataset('main', $this->model);
$csv = $template->render();

$this->setTemplate(false);
$this->response->headers->set('Content-type', 'text/csv; charset=utf-8');
$this->response->headers->set('Content-Disposition', 'attachment;filename=mi-informe.csv');
$this->response->setContent($csv);
```

La API es idéntica a la de `PDFTemplate`: mismo `loadTemplate()`, mismo `addDataset()`, mismo `render()`.


## 📋 Varios grupos

Si el informe tiene varios grupos, el CSV exporta cada uno de forma secuencial. Cada grupo añade su propia fila de cabecera de campos antes de sus filas de datos:

```
"codigo";"nombre";"importe"
"A001";"Artículo uno";"150.00"
"codigo";"categoria";"descripcion"
"CAT1";"Electrónica";"Descripción cat 1"
```


## 💡 Reutilizar el mismo XML

No necesitas un XML específico para CSV. Usa el mismo que tienes para PDF: el motor ignora automáticamente lo que no tiene sentido en CSV (cabeceras, pies, líneas, imágenes, totales calculados) y extrae únicamente los valores de datos del detalle.
