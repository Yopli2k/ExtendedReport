En algunos informes, cada registro del dataset necesita **varias filas visuales apiladas** en lugar de una sola. Por ejemplo, una comparativa de periodos donde cada artículo muestra tres líneas (actual / anterior / diferencia), o una ficha con nombre y código apilados.

El atributo **`subrows`** de la banda `<detail>` indica al visor HTML cuántas subfilas componen cada registro.

> `subrows` solo afecta al **visor HTML**. En PDF, las columnas ya se posicionan por coordenadas absolutas (`posx`/`posy`), por lo que el apilado es natural sin necesidad de este atributo.


## ⚙️ Cómo funciona

Con `subrows="N"`, el motor HTML agrupa en **un único `<tr>`** todas las columnas del registro, apilando los valores por `posy` dentro de cada celda de la cuadrícula. Cada grupo de columnas con un `posy` similar forma una subfila visual.

```xml
<detail height="54" subrows="3">

    <!-- subfila 0 (posy=0): valores actuales -->
    <column posx="20" posy="0" width="160">
        <widget type="label" fieldname="nombre" bold="true" />
    </column>
    <column posx="180" posy="0" width="80">
        <widget type="number" fieldname="actual[1]" />
    </column>

    <!-- subfila 1 (posy=20): valores anteriores -->
    <column posx="20" posy="20" width="160">
        <widget type="label" fieldname="codigo" italic="true" />
    </column>
    <column posx="180" posy="20" width="80">
        <widget type="number" fieldname="anterior[1]" />
    </column>

    <!-- subfila 2 (posy=40): diferencias -->
    <column posx="180" posy="40" width="80">
        <widget type="number" fieldname="diferencia[1]" negative="red" />
    </column>

</detail>
```

En este ejemplo, cada registro ocupa tres líneas visuales en pantalla: la primera con nombre y valor actual, la segunda con código y valor anterior, y la tercera con la diferencia.


## 📊 El pie también apila

El `<footer>` hereda automáticamente el comportamiento de apilado del `<detail>`: si el detail tiene `subrows > 1`, el pie también agrupa sus columnas por `posy` en un único `<tr>` del `<tfoot>`. No es necesario indicar `subrows` en el footer.

```xml
<footer height="54">
    <column posx="20" posy="0" width="120">
        <widget type="label" value="total" translate="true" align="right" bold="true" />
    </column>
    <!-- totales de la subfila 0 -->
    <column posx="180" posy="0" width="80">
        <widget type="calculated" operator="sum" fieldname="actual[1]" bold="true" />
    </column>
    <!-- totales de la subfila 1 -->
    <column posx="180" posy="20" width="80">
        <widget type="calculated" operator="sum" fieldname="anterior[1]" bold="true" />
    </column>
    <!-- totales de la subfila 2 -->
    <column posx="180" posy="40" width="80">
        <widget type="calculated" operator="sum" fieldname="diferencia[1]" bold="true" />
    </column>
</footer>
```


## 📐 Tolerancia de agrupación por `posy`

El motor agrupa en la misma subfila todas las columnas cuyo `posy` difiere en **menos de 8 puntos**. Si dos columnas que quieres en la misma fila se separan en subfilas distintas, acerca sus `posy` para que queden dentro de esa tolerancia.


## 📌 Ejemplo de referencia

El plugin incluye el diseño de ejemplo `ReportTestMultiline.xml` (formato landscape, 3 periodos × 3 subfilas por fila). Puedes lanzarlo desde el controlador `ReportTest` del propio plugin (acción `html-multiline-test`).
