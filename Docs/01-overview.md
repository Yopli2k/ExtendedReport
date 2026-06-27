# ExtendedReport — Visión General

## ¿Qué es ExtendedReport?

**ExtendedReport** es un plugin para FacturaScripts pensado para **desarrolladores**. Proporciona un motor que genera informes en **PDF**, **CSV** y **HTML en pantalla** a partir de dos piezas:

1. Un **diseño en XML** que describe cómo se ve el informe (posiciones, textos, columnas, líneas, imágenes, totales…).
2. Un **modelo de datos PHP** que aporta las filas a representar.

La idea central es **separar el diseño de los datos**. El XML define la maqueta del documento de forma declarativa —al estilo de los `XMLView` del core, pero orientado a impresión— y el código PHP solo se encarga de obtener los datos y lanzar el render. Esto permite crear informes a medida (listados, fichas, certificados, etiquetas, resúmenes contables, etc.) sin tener que programar a mano cada coordenada de dibujo sobre el PDF.

Por debajo, el plugin usa la librería **R&OS PDF (Cezpdf)**, que es la misma que FacturaScripts emplea para generar sus PDF. La documentación de esa librería está en la carpeta `Docs/ROS PDF/` y es la referencia de bajo nivel sobre la que se apoya ExtendedReport.


## ¿Para qué sirve? Casos de uso

ExtendedReport no es un plugin que el usuario final "use" directamente desde un menú: es una **herramienta para construir informes** dentro de otros plugins. Algunos escenarios típicos:

### Listados e informes personalizados
Generar un PDF con un listado de registros (clientes, facturas, movimientos de stock…) con cabecera de empresa, columnas alineadas, líneas separadoras y totales al pie, sin maquetar manualmente.

### Documentos con agrupaciones y subtotales
Informes en los que los datos se agrupan por un campo (por ejemplo, ventas agrupadas por familia o por agente) mostrando una cabecera por grupo, sus líneas de detalle y un subtotal cuando cambia el valor del campo de ruptura.

### Fichas o certificados
Documentos de una o varias páginas con textos fijos, datos de la empresa/usuario, fechas, numeración de página e imágenes (incluida la imagen de un producto a partir de su referencia).

### Exportación a CSV reutilizando el mismo diseño
A partir del **mismo XML**, obtener un CSV con las columnas del detalle, sin escribir un exportador aparte.

### Visualización en pantalla como HTML
Renderizar el mismo informe como una **tabla HTML responsive** en el navegador: en pantalla sin descargar nada, con los mismos datos, agrupaciones y totales que el PDF. Útil para previsualizar antes de imprimir o para ofrecer al usuario una vista interactiva.


## Cómo funciona (arquitectura)

El flujo de generación combina cuatro elementos:

```
   Diseño XML            Datos PHP
 (XMLView/Report)      (ModelReport)
        │                    │
        ▼                    ▼
  ┌─────────────────────────────────────┐
  │          ExportTemplate             │   ← carga el XML y los datasets
  │  ┌──────────┐ ┌───────────┐ ┌────┐ │
  │  │PDFTemplate│ │CSVTemplate│ │HTML│ │   ← motores de render concretos
  │  └──────────┘ └───────────┘ └────┘ │
  └─────────────────────────────────────┘
        │                          │
        ▼                          ▼
   Cezpdf (R&OS PDF)         Twig (HTML)
   → PDF / CSV final       → tabla en pantalla
```

1. **El diseño XML** se guarda en `XMLView/Report/NombreInforme.xml` y describe la estructura (config + grupos + bandas + columnas + widgets).
2. **El modelo de datos** extiende `ModelReport` e implementa `loadData()`, dejando las filas en su propiedad `data` (un array de objetos).
3. **La plantilla de exportación** (`PDFTemplate`, `CSVTemplate` o `HtmlTemplate`, todas hijas de `ExportTemplate`) carga el XML con `loadTemplate()`, recibe los datos con `addDataset()` y produce la salida con `render()`.
4. **`render()`** recorre el diseño, vuelca cada widget en su posición y devuelve el contenido del PDF/CSV listo para enviar en la respuesta.

### El esqueleto de un diseño XML

```xml
<report>
    <config>
        <page type="A4" orientation="portrait" />
        <font type="Arial" size="12" />
        <default group="main" alter="other" />
    </config>

    <group name="main">
        <header height="150"> ... columnas ... </header>
        <detail height="20">  ... columnas ... </detail>
        <footer height="65">  ... columnas ... </footer>
    </group>
</report>
```

- **`config`** — configuración general: tamaño y orientación de página, fuente y grupo por defecto.
- **`group`** — agrupa las tres bandas del informe. Puede anidar otro `group` dentro del detalle para crear **subgrupos con ruptura**.
- **`header` / `detail` / `footer`** — las tres **bandas**:
  - El **header** se pinta al inicio (y al inicio de cada página).
  - El **detail** se repite **una vez por cada fila** del dataset.
  - El **footer** se pinta al final (y al pie de cada página), y es donde se ubican los totales calculados.
- **`column`** — define una celda con su posición (`posx`, `posy`), `width` y `height`.
- **`widget`** — el contenido que se dibuja dentro de la columna.

### Conceptos clave

- **Bandas (bands):** cabecera, detalle y pie. Cada una contiene columnas. El motor controla automáticamente los saltos de página, repintando cabecera y pie según haga falta.
- **Ruptura de grupo (`fieldname` en `detail`):** cuando el valor de ese campo cambia entre filas, se cierra el subgrupo anterior (pintando su pie/subtotal) y se abre uno nuevo (pintando su cabecera). Permite subtotales por categoría.
- **Columnas calculadas:** el widget `calculated` acumula valores a lo largo del detalle (`sum`, `count`, `avg`, `min`, `max`) y muestra el resultado en el pie del grupo.
- **Datasets:** se asocian por nombre (`addDataset('main', $model)`); cada grupo del XML toma su dataset por su `name`, con `main` como valor por defecto.


## Tipos de widget disponibles

Cada `column` contiene un `widget type="..."`. Los tipos incluidos son:

- **`label`** (`WidgetLabel`) — Texto: literal (`value`) o campo del dato (`fieldname`). Admite `align`, `bold`, `italic`, `underline`, `size`, `color`, `bgcolor`, `translate`, `class`.
- **`default`** (`WidgetDefault`) — Textos automáticos del sistema: `company.*`, `user.*`, `date`, `time`, `page` y datos `additional`.
- **`number`** (`WidgetNumber`) — Valores numéricos con formato (decimales, separadores, divisa, iconos, color para negativos con `negative`).
- **`calculated`** (`WidgetCalculated`) — Totales acumulados en el pie: `sum`, `count`, `avg`, `min`, `max`.
- **`line`** (`WidgetLine`) — Líneas separadoras o rectángulos (con grosor `border`, `height` y `bgcolor`).
- **`image`** (`WidgetImage`) — Imagen desde una ruta, con opción de generar miniatura (`resize`).
- **`imageproduct`** (`WidgetImageproduct`) — Imagen de un producto a partir de su `referencia`.

> El nombre de la clase se resuelve dinámicamente como `Widget` + el `type` capitalizado, así que añadir un widget nuevo es tan sencillo como crear su clase en `Lib/WidgetReport/`.

### Resolución de `fieldname`

El `fieldname` de un widget admite varias formas para acceder al dato de la fila:

- **Campo directo:** `amount` → `$row->amount`.
- **Propiedad de objeto relacionado:** `company.nombre` → `$row->company->nombre`.
- **Elemento de array:** `datos[clave]`.
- **Llamada a método:** `getTotal()` o `getTotal('param')`.


## Componentes del plugin

```
ExtendedReport/
├── Lib/
│   ├── ExtendedReport/
│   │   ├── ExportTemplate.php    → clase base (carga XML + datasets)
│   │   ├── PDFTemplate.php       → motor de render a PDF (Cezpdf)
│   │   ├── CSVTemplate.php       → motor de render a CSV
│   │   ├── HtmlTemplate.php      → motor de render a HTML en pantalla
│   │   ├── PDFReport.php         → ayudante de alto nivel (carga + muestra el PDF)
│   │   └── ModelReport.php       → clase base de los modelos de datos
│   └── WidgetReport/
│       ├── ReportItemLoadEngine.php → parsea el XML a objetos
│       ├── GroupItem / BandItem / BandHeader / BandDetail / BandFooter
│       ├── ColumnItem / ConfigItem / ReportDefaultData
│       └── Widget*.php           → los widgets de pintado
├── XMLView/Report/              → diseños XML de los informes
├── Controller/ReportTest.php    → controlador de ejemplo/prueba
├── Controller/ReportHtmlViewer.php → visor HTML del informe (nueva pestaña)
├── Model/Report/                → modelos de datos de ejemplo (TestReport, TestData)
├── View/ReportTest.html.twig    → vista del controlador de prueba
├── View/ReportHtmlViewer.html.twig → vista del visor HTML
└── View/Block/ReportHtml.html.twig → bloque Twig reutilizable con la tabla del informe
```


## Ejemplo de prueba incluido

El plugin trae un controlador de demostración, **ReportTest** (visible en el menú *Admin* mientras `showonmenu` esté a `true`), que permite:

- **Imprimir un informe de prueba** con datos aleatorios usando el diseño `ReportTest.xml` (cabecera de empresa, listado y pie con `count`, `min`, `max` y `sum`).
- **Visualizar el informe en pantalla como HTML**, usando el mismo diseño y datos — accesible desde el botón de previsualización HTML.
- **Imprimir plantillas de referencia de columnas** (`ColumnTestPortrait` / `ColumnTestLandscape`) que dibujan una regla de coordenadas, útil para **calibrar los `posx`/`posy`** al maquetar un diseño nuevo.

Su código es la mejor referencia práctica del flujo mínimo:

```php
$template = new PDFTemplate($this->user, $this->empresa);
$template->loadTemplate('ReportTest');     // carga XMLView/Report/ReportTest.xml
$template->addDataset('main', $this->model); // datos (ModelReport)
$pdf = $template->render();                 // genera el PDF
```


## Flujo básico de uso (para un desarrollador)

```
1. Crear el modelo de datos (extiende ModelReport, implementa loadData)
         ↓
2. Diseñar el informe en XMLView/Report/MiInforme.xml
         ↓
3. En el controlador: instanciar PDFTemplate / CSVTemplate / HtmlTemplate
         ↓
4. loadTemplate('MiInforme') + addDataset('main', $modelo)
         ↓
5. render() y enviar el resultado:
       PDF  → application/pdf
       CSV  → text/csv
       HTML → string Twig a incrustar en una vista o abrir en nueva pestaña
```


## Requisitos

- FacturaScripts **2025.8** o superior (ver `facturascripts.ini`).
- Conocimientos de PHP y de la estructura de plugins de FacturaScripts (es un plugin de desarrollo).
- Para diseños avanzados, conviene tener a mano la documentación de **R&OS PDF** incluida en `Docs/ROS PDF/`, ya que los widgets se apoyan en las primitivas de dibujo de esa librería.
