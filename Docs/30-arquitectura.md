Este documento describe la estructura interna del plugin: las clases principales, sus responsabilidades y cómo se relacionan entre sí. Es una referencia para desarrolladores que quieran extender el motor o entender el flujo de render en detalle.


## 🗂️ Mapa de clases

```
ExportTemplate (abstracta)
├── PDFTemplate       → render a PDF usando Cezpdf
├── CSVTemplate       → render a CSV (texto separado por ';')
└── HtmlTemplate      → render a HTML en pantalla (Bootstrap)

PDFReport             → ayudante de alto nivel que envuelve PDFTemplate

ModelReport (abstracta)
└── [tus modelos]     → extienden ModelReport e implementan loadData()

ReportItemLoadEngine  → parsea el XML y construye la estructura de objetos

ConfigItem            → la etiqueta <config> (página, fuente, defaults)

GroupItem             → la etiqueta <group> (agrupa las tres bandas)

BandItem (abstracta)
├── BandHeader        → la etiqueta <header>
├── BandDetail        → la etiqueta <detail> (fieldName, subRows)
└── BandFooter        → la etiqueta <footer> (calculate, reset)

ColumnItem            → la etiqueta <column> (posx, posy, width, height, area, hideOnView, hideOnPdf)

WidgetItem (abstracta)
├── WidgetLine        → type="line"
├── WidgetImage       → type="image"
│   └── WidgetImageproduct → type="imageproduct"
└── WidgetLabel       → type="label"
    ├── WidgetDefault → type="default"
    └── WidgetNumber  → type="number"
        └── WidgetCalculated → type="calculated"

ReportDefaultData     → datos de contexto (empresa, usuario, fecha, página, additional)
```


## 🔄 Flujo de render (PDF)

1. `PDFTemplate::loadTemplate('Nombre')` → `ReportItemLoadEngine::installXML()` parsea el XML y rellena `$this->config` (ConfigItem) y `$this->groups` (array de GroupItem).

2. `PDFTemplate::addDataset('main', $model)` → almacena el modelo en `$this->datasets['main']`.

3. `PDFTemplate::render()` → para cada `GroupItem`:
    - `renderHeader(group)` → pinta la cabecera (`BandHeader`).
    - `renderDetail(group)` → recorre `$model->data`:
        - Por cada fila, detecta si hay ruptura (`BandDetail::hasFieldRupture()`).
        - Si hay ruptura y hay subgrupo: pinta el pie del subgrupo, reinicia los calculated, pinta la cabecera del nuevo subgrupo.
        - Calcula el espacio restante en la página; si la fila no cabe, hace salto de página.
        - Pinta la fila de detalle (`BandDetail::render()`).
        - Acumula los widgets calculated del footer (`BandFooter::calculate()`).
    - `renderFooter(group)` → pinta el pie (`BandFooter`).

4. `PDFTemplate::render()` devuelve el PDF binario (`Cezpdf::output()`).


## 📄 Flujo de render (HTML)

`HtmlTemplate::render()` construye un array PHP con la estructura semántica de la tabla (meta, thead, tbody, tfoot, stats) y lo pasa a la plantilla Twig `Block/ReportHtml.html.twig`, que genera el HTML final.

El array tiene esta forma por grupo:
- `columns`: número de columnas de la cuadrícula.
- `meta`: filas del bloque de metadatos (columnas con `area="meta"`).
- `stats`: tarjetas de resumen (columnas con `area="cards"`).
- `thead`: filas de cabecera de la tabla.
- `tbody`: filas de datos y subgrupos.
- `tfoot`: filas del pie.


## 🔧 ReportDefaultData

`ReportDefaultData` encapsula los datos de contexto del informe que no son filas del dataset: la empresa, el usuario, el número de página actual, la fecha/hora del sistema y el array `additional`. Los widgets `default` llaman a sus métodos (`getCompanyFieldValue`, `getUserFieldValue`, `getPageNum`, etc.) para obtener sus valores.


## 📦 ReportItemLoadEngine

`ReportItemLoadEngine` lee el XML, convierte cada nodo en su clase correspondiente y los monta en el árbol de objetos que usa `ExportTemplate`. La resolución de clase del widget es dinámica: dado `type="label"` busca `WidgetLabel`; dado `type="mitipo"` buscaría `WidgetMitipo`. Esto es lo que hace tan sencillo añadir widgets nuevos sin modificar el parser.
