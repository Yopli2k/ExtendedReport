Un **grupo** (`<group>`) es el contenedor que agrupa las tres bandas de una sección del informe: cabecera, detalle y pie. El atributo `name` del grupo lo identifica y sirve para emparejarlo con su dataset en el controlador.

Un informe puede tener varios grupos, que se renderizan de forma secuencial en el orden en que aparecen en el XML.


## 🧩 La etiqueta `group`

Atributos:
- **`name`** (obligatorio): identificador único del grupo. Debe coincidir con el nombre usado en `addDataset('name', $model)`. Si no existe dataset con ese nombre, se usa el dataset `main` como respaldo.

```xml
<group name="main">
    <header ...> ... </header>
    <detail ...>  ... </detail>
    <footer ...>  ... </footer>
</group>
```

Un grupo también puede contener otro `<group>` en lugar de un `<detail>`, lo que crea un **subgrupo con ruptura**. Ver **[20 - Ruptura de subgrupos](20-ruptura-subgrupos.md)**.


## 🗂️ Las tres bandas

Cada grupo puede tener hasta tres bandas. Todas comparten el atributo `height` (obligatorio) y el atributo `type` (opcional).

- **`height`**: altura de la banda en puntos. El motor usa este valor para avanzar la posición vertical y decidir si cabe en la página actual o hay que hacer un salto.
- **`type`**: `main` (por defecto) o `second`. Permite tener una variante de cabecera o pie para páginas posteriores a la primera. Ver más abajo.


### 📌 header — cabecera

Se pinta al inicio del grupo y al inicio de cada página nueva.

Atributo adicional:
- **`newpage`**: si es `true`, fuerza un salto de página antes de pintar esta cabecera. Por defecto `false`.

```xml
<header height="150">
    <column posx="20" posy="25" width="540">
        <widget type="default" fieldname="company.nombre"
                size="12" align="left" bgcolor="4169E1" color="white" />
    </column>
    <!-- más columnas ... -->
</header>
```


### 📋 detail — detalle

Se repite una vez por cada fila del dataset. Es la única banda que acepta el campo de ruptura de subgrupo.

Atributos adicionales:
- **`fieldname`**: nombre del campo que provoca la **ruptura de subgrupo** cuando su valor cambia entre filas consecutivas. Ver **[20 - Ruptura de subgrupos](20-ruptura-subgrupos.md)**.
- **`subrows`**: número de subfilas visuales que componen cada fila en el **visor HTML** (por defecto `1`). Útil para informes con varias líneas apiladas por registro. Ver **[21 - Informes multilinea](21-multilinea.md)**.

```xml
<detail height="20">
    <column posx="20" width="50">
        <widget type="label" fieldname="codigo" />
    </column>
    <column posx="80" width="250">
        <widget type="label" fieldname="nombre" />
    </column>
</detail>
```


### 📊 footer — pie

Se pinta al final del grupo y al pie de cada página. Es donde normalmente se colocan los totales con `widget type="calculated"`.

Atributos adicionales:
- **`newpage`**: si es `true`, fuerza un salto de página después de pintar el pie. Por defecto `false`.
- **`placebottom`**: si es `true`, ancla el pie a la parte inferior de la página. Por defecto `false`.

```xml
<footer height="65">
    <column posx="10" posy="1" width="550" height="1">
        <widget type="line" />
    </column>
    <column posx="460" posy="30" width="80">
        <widget type="calculated" operator="sum" fieldname="importe"
                align="right" bold="true" />
    </column>
</footer>
```


## 🔄 Variantes main y second

Las bandas `header` y `footer` pueden definirse dos veces: una con `type="main"` (o sin atributo `type`) y otra con `type="second"`. Esto permite que la **primera página** muestre una versión distinta de la que se pinta en las **páginas siguientes**.

Ejemplo: cabecera completa en la primera página, cabecera reducida en las siguientes:

```xml
<!-- primera página: cabecera completa con logo y datos -->
<header height="150" type="main">
    <column posx="20" posy="25" width="540">
        <widget type="default" fieldname="company.nombre" size="14" bold="true" />
    </column>
    <!-- logo, dirección, teléfono... -->
</header>

<!-- páginas siguientes: solo el nombre -->
<header height="40" type="second">
    <column posx="20" posy="10" width="540">
        <widget type="default" fieldname="company.nombre" size="10" />
    </column>
</header>
```

Si no se define la variante `second`, el motor reutiliza la `main` en todas las páginas.
