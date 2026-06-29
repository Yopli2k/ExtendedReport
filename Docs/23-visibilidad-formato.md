Algunos elementos de un informe solo tienen sentido en un formato concreto. El número de página, por ejemplo, no tiene utilidad en el visor HTML porque la página web no se divide en páginas físicas. A la inversa, una nota de filtros activos puede ser relevante en pantalla pero sobrar en el PDF impreso.

Los atributos **`hideonview`** y **`hideonpdf`** de la etiqueta `<column>` permiten controlar en qué formatos se muestra cada columna.


## 🙈 hideonview — ocultar en el visor HTML

Si es `true`, la columna **no se muestra en el visor HTML**. Se pinta con normalidad en PDF y se exporta en CSV si tiene `fieldname`.

Por defecto `false`.

Casos habituales:
- Número de página (`widget type="default" fieldname="page"`): no tiene sentido en HTML.
- Títulos del informe con coordenadas específicas de PDF que se sustituyen por el bloque `meta` en HTML.
- Líneas decorativas: aunque los widgets `line` ya se descartan automáticamente en HTML, puedes usar `hideonview` para asegurarte de que una columna completa no aparezca.

```xml
<!-- número de página: solo en PDF -->
<column posx="500" posy="47" width="60" hideonview="true">
    <widget type="label" value="page" translate="true" size="12" />
</column>
<column posx="560" posy="47" width="30" hideonview="true">
    <widget type="default" fieldname="page" size="12" />
</column>

<!-- título centrado del informe: solo en PDF (en HTML el título va en el bloque meta) -->
<column posx="20" posy="85" width="540" hideonview="true">
    <widget type="label" value="mi-informe" translate="true"
            align="center" bold="true" size="18" />
</column>
```


## 🙉 hideonpdf — ocultar en el PDF

Si es `true`, la columna **no se pinta en el PDF**. Se muestra en el visor HTML y se exporta en CSV si tiene `fieldname`.

Por defecto `false`.

Casos habituales:
- Columnas con información que solo es relevante en pantalla (notas interactivas, enlaces, etc.).
- Columnas que aportan contexto en HTML pero ocuparían espacio innecesario en el PDF.

```xml
<!-- nota de filtros: solo en pantalla -->
<column posx="20" posy="60" width="540" hideonpdf="true" area="meta">
    <widget type="default" fieldname="filtros.resumenFiltros()" prewrap="true" />
</column>
```


## 📌 Combinaciones útiles

Número de página solo en PDF, ignorado en HTML:
```xml
<column posx="500" posy="10" width="60" hideonview="true">
    <widget type="default" fieldname="page" />
</column>
```

Tarjeta de resumen solo como card en HTML (sin aparecer en la fila del pie):
```xml
<column posx="460" posy="30" width="80" area="cards" hideonview="true">
    <widget type="calculated" operator="sum" fieldname="importe"
            title="total" cardcolor="success" />
</column>
```

Columna que aparece en HTML pero no en PDF:
```xml
<column posx="20" posy="10" width="300" hideonpdf="true" area="meta">
    <widget type="default" fieldname="filtros.texto" />
</column>
```
