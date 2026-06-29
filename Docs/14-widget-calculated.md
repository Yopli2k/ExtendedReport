El widget `calculated` acumula valores a lo largo del detalle y muestra el resultado en el **pie del grupo**. Hereda de `number`, por lo que admite todos sus atributos de formato (decimales, divisa, color para negativos, etc.).

```xml
<widget type="calculated" operator="sum" fieldname="importe" align="right" bold="true" />
```


## ⚙️ Atributo específico: `operator`

Define la operación de agregación. Por defecto `sum`.

Operadores disponibles:
- **`sum`**: suma de los valores del campo en todas las filas.
- **`count`**: recuento de filas (no usa `fieldname` para calcular, lo usa solo para identificar el widget).
- **`avg`**: media aritmética incremental.
- **`min`**: valor mínimo encontrado.
- **`max`**: valor máximo encontrado.


## 🔄 Cómo funciona

El motor llama internamente al método `process()` del widget por cada fila del detalle, acumulando el resultado. Cuando llega al pie del grupo, el widget ya tiene el total final y lo muestra al pintarse.

Esto significa que:
- El widget `calculated` **debe estar en una banda `footer`** para funcionar correctamente.
- El `fieldname` que se indica es el campo del registro del que se extrae el valor a acumular.
- En informes con **ruptura de subgrupo**, los totales del subgrupo se **reinician automáticamente** (`reset()`) tras pintar cada pie de subgrupo, de modo que cada bloque muestra sus propios subtotales. Ver **[20 - Ruptura de subgrupos](20-ruptura-subgrupos.md)**.


## 📌 Ejemplos

Suma del importe:
```xml
<footer height="30">
    <column posx="460" posy="10" width="80">
        <widget type="calculated" operator="sum" fieldname="importe"
                align="right" bold="true" />
    </column>
</footer>
```

Recuento de filas:
```xml
<column posx="85" posy="30" width="80">
    <widget type="calculated" operator="count" fieldname="codigo" align="left" bold="true" />
</column>
```

Valor máximo y mínimo:
```xml
<column posx="170" posy="30" width="80">
    <widget type="calculated" operator="min" fieldname="importe" align="left" bold="true" />
</column>

<column posx="320" posy="30" width="80">
    <widget type="calculated" operator="max" fieldname="importe" align="left" bold="true" />
</column>
```

Suma con divisa:
```xml
<column posx="460" posy="10" width="90">
    <widget type="calculated" operator="sum" fieldname="total"
            currency="coddivisa" align="right" bold="true" />
</column>
```


## 🃏 Tarjetas resumen en el visor HTML

En el visor HTML, un total del pie puede mostrarse también como una **tarjeta de color pastel** sobre la tabla, añadiendo `area="cards"` a la columna y los atributos `title` y `cardcolor` al widget:

```xml
<column posx="460" posy="30" width="80" area="cards">
    <widget type="calculated" operator="sum" fieldname="importe"
            align="right" bold="true"
            title="total" cardcolor="success" />
</column>
```

Ver **[22 - Áreas meta y cards](22-areas-meta-cards.md)** para más detalles.
