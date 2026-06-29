El diseño del informe se define en un archivo XML que describe qué se muestra, cómo se posiciona y con qué formato. Es el equivalente a los `XMLView` del core, pero orientado a impresión y a la generación de PDF, CSV y HTML.


## 📁 Dónde se guarda

Los archivos XML de informe se colocan en la carpeta `XMLView/Report/` del plugin que los usa:

```
TuPlugin/XMLView/Report/MiInforme.xml
```

El motor los busca a través del sistema **Dinamic** (`Dinamic/XMLView/Report/MiInforme.xml`), por lo que después de crear o modificar un XML hay que **reconstruir/desplegar** los plugins para que el cambio sea efectivo.

El nombre que se pasa a `loadTemplate('MiInforme')` es el del archivo **sin extensión**.


## 🧾 Estructura general

Todo archivo de informe sigue esta jerarquía de etiquetas:

```
report
├── config                  → configuración global (página, fuente)
└── group (1 o más)         → cada sección de datos del informe
    ├── header              → banda de cabecera
    ├── detail              → banda de detalle (una vez por fila)
    │   └── column (n)      → celda posicionada
    │       └── widget      → contenido a pintar
    └── footer              → banda de pie (totales, resumen)
        └── column (n)
            └── widget
```

Un informe tiene siempre al menos un `group`. El `config` es opcional pero recomendable para fijar el tamaño y la orientación de página.


## 🏷️ Etiquetas principales

- **`report`**: etiqueta raíz. No tiene atributos.
- **`config`**: configuración global del informe. Ver **[05 - La etiqueta config](05-config.md)**.
- **`group`**: agrupa las bandas de una sección del informe. Tiene el atributo obligatorio `name`. Ver **[06 - Grupos y bandas](06-grupos-y-bandas.md)**.
- **`header`**: banda de cabecera del grupo. Se pinta al inicio y en cada nueva página.
- **`detail`**: banda de detalle. Se repite una vez por cada fila del dataset.
- **`footer`**: banda de pie del grupo. Se pinta al final y al pie de cada página.
- **`column`**: celda posicionada dentro de una banda. Contiene exactamente un `widget`. Ver **[07 - La etiqueta column](07-column.md)**.
- **`widget`**: el contenido que se dibuja dentro de la columna. Ver **[10 - Widgets](10-widgets-introduccion.md)**.


## 📄 Ejemplo completo mínimo

```xml
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
                <widget type="label" value="Mi informe" bold="true" size="16" />
            </column>
        </header>

        <detail height="20">
            <column posx="20" width="200">
                <widget type="label" fieldname="nombre" />
            </column>
            <column posx="460" width="80">
                <widget type="number" fieldname="importe" align="right" />
            </column>
        </detail>

        <footer height="25">
            <column posx="460" posy="5" width="80">
                <widget type="calculated" operator="sum" fieldname="importe"
                        align="right" bold="true" />
            </column>
        </footer>

    </group>
</report>
```


## 🔢 Varios grupos

Un informe puede tener más de un `group`. Los grupos se renderizan en el orden en que aparecen en el XML, cada uno con su propio dataset:

```xml
<report>
    <config> ... </config>

    <group name="ventas">
        ...
    </group>

    <group name="compras">
        ...
    </group>
</report>
```

En el controlador asocias cada dataset por nombre:

```php
$template->addDataset('ventas', $modelVentas);
$template->addDataset('compras', $modelCompras);
```

Si un grupo no encuentra su dataset por nombre, usa el dataset llamado `main` como respaldo.
