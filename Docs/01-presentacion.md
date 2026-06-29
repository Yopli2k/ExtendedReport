**ExtendedReport** es un plugin para FacturaScripts que proporciona el motor necesario para crear informes a medida dentro de otros plugins. Permite generar documentos en **PDF**, exportarlos en **CSV** y visualizarlos en pantalla como **HTML**, todo ello a partir de dos piezas que el desarrollador crea en su propio plugin:

- Un **modelo de datos PHP** que obtiene las filas a representar.
- Un **diseño XML** que describe cómo se presenta la información.

La idea central es separar el diseño de los datos. El XML define la maqueta del documento de forma declarativa —al estilo de los `XMLView` del core, pero orientado a impresión— y el código PHP solo se encarga de obtener los datos y lanzar la generación. Esto evita tener que programar manualmente cada coordenada sobre el PDF.

Por debajo, el plugin usa la librería **R&OS PDF (Cezpdf)**, la misma que utiliza FacturaScripts internamente para generar sus propios documentos PDF.


## ⚠️ A quién va dirigido

Este plugin **no está pensado para el usuario final**. No añade ninguna opción de menú ni funcionalidad directamente usable. Es una herramienta para **desarrolladores** que crean plugins y quieren incluir informes propios sin partir de cero.

Para usarlo necesitas conocimientos de:
- Creación de plugins en FacturaScripts.
- PHP orientado a objetos.
- Estructura básica de los archivos XML del core (XMLView).


## 📋 Casos de uso habituales

- **Listados e informes personalizados**: PDF con cabecera de empresa, filas de datos, líneas separadoras y totales al pie.
- **Informes con agrupaciones y subtotales**: datos agrupados por categoría, familia o agente, con una cabecera por grupo y un subtotal al cerrar cada bloque.
- **Fichas y certificados**: documentos con datos fijos, datos de empresa, fecha, numeración de página e imágenes.
- **Exportación a CSV**: a partir del mismo XML del PDF, obtener un fichero de datos sin escribir un exportador separado.
- **Visualización en pantalla**: el mismo informe renderizado como tabla HTML responsive en el navegador, sin descargar nada.


## 🔄 Flujo básico

```
  Diseño XML                 Modelo PHP
(XMLView/Report/)          (ModelReport)
        │                       │
        └──────────┬────────────┘
                   ▼
           ExportTemplate
      (carga XML + datasets)
                   │
        ┌──────────┼───────────┐
        ▼          ▼           ▼
  PDFTemplate  CSVTemplate  HtmlTemplate
        │          │           │
       PDF        CSV         HTML
```

1. El **diseño XML** se guarda en `XMLView/Report/NombreInforme.xml` del plugin.
2. El **modelo de datos** extiende `ModelReport` e implementa `loadData()`, dejando las filas en `$this->data`.
3. En el **controlador** se instancia la plantilla de render (`PDFTemplate`, `CSVTemplate` o `HtmlTemplate`), se carga el XML y se asocian los datos.
4. Se llama a `render()` y se envía el resultado al usuario.


## 📁 Dónde vive cada pieza en tu plugin

```
TuPlugin/
├── Model/
│   └── Report/
│       ├── MiInforme.php         ← modelo de datos (extiende ModelReport)
│       └── Data/
│           └── MiInformeData.php ← clase con la estructura de cada fila (opcional)
├── XMLView/
│   └── Report/
│       └── MiInforme.xml         ← diseño del informe
└── Controller/
    └── MiControlador.php         ← lanza la generación y envía la respuesta
```

Consulta los siguientes documentos para ir construyendo cada pieza:
- **[02 - Guía rápida](02-guia-rapida.md)**: de cero a un informe funcionando.
- **[03 - El modelo de datos](03-modelo-de-datos.md)**: cómo crear y estructurar `ModelReport`.
- **[04 - El diseño XML](04-diseno-xml.md)**: estructura del archivo XML del informe.
