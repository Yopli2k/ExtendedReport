La **ruptura de subgrupo** permite organizar los datos del informe en bloques, mostrando una cabecera al inicio de cada bloque, sus líneas de detalle y un subtotal al cerrar el bloque. Esto es lo que normalmente se llama un informe con ruptura de secuencia o informe agrupado.


## 🧩 Cómo se define

En lugar de una banda `<detail>` directa, el grupo contiene **otro `<group>` anidado**. Ese subgrupo tiene sus propias bandas de cabecera, detalle y pie.

La ruptura se activa con el atributo **`fieldname`** en la banda `<detail>` del subgrupo: cuando el valor de ese campo cambia entre filas consecutivas, el motor cierra el bloque anterior (pintando su pie/subtotal) y abre uno nuevo (pintando su cabecera).

```xml
<group name="main">

    <!-- cabecera general del informe -->
    <header height="60">
        <column posx="20" posy="15" width="540">
            <widget type="label" value="Ventas por categoría"
                    bold="true" size="16" align="center" />
        </column>
    </header>

    <!-- subgrupo: se repite por cada categoría -->
    <group name="categoria">
        <header height="20">
            <column posx="20" posy="5" width="540">
                <widget type="label" fieldname="categoria" bold="true" bgcolor="silver" />
            </column>
        </header>

        <detail height="18" fieldname="categoria">
            <column posx="30" width="250">
                <widget type="label" fieldname="nombre" />
            </column>
            <column posx="460" width="80">
                <widget type="number" fieldname="importe" align="right" />
            </column>
        </detail>

        <footer height="20">
            <column posx="380" posy="3" width="70">
                <widget type="label" value="subtotal" translate="true"
                        align="right" bold="true" />
            </column>
            <column posx="460" posy="3" width="80">
                <widget type="calculated" operator="sum" fieldname="importe"
                        align="right" bold="true" />
            </column>
        </footer>
    </group>

    <!-- pie general con el total global -->
    <footer height="25">
        <column posx="380" posy="5" width="70">
            <widget type="label" value="total" translate="true"
                    align="right" bold="true" />
        </column>
        <column posx="460" posy="5" width="80">
            <widget type="calculated" operator="sum" fieldname="importe"
                    align="right" bold="true" />
        </column>
    </footer>

</group>
```


## 🔄 Qué ocurre en cada ruptura

Cuando el valor del campo `fieldname` cambia entre dos filas consecutivas:

1. Se pinta el **pie del subgrupo** con los totales acumulados del bloque que termina.
2. Se **reinician** los widgets `calculated` del subgrupo, para que el siguiente bloque empiece desde cero.
3. Si está activo el salto por ruptura, se crea una **página nueva** (ver más abajo).
4. Se pinta la **cabecera del subgrupo** para el nuevo valor.


## ⚠️ El orden de los datos es obligatorio

La ruptura detecta **cambios entre filas consecutivas**. Para que los subtotales sean correctos, el dataset debe venir **ordenado por el campo de ruptura**. ExtendedReport no ordena los datos: ese orden lo decides en el método `loadData()` de tu modelo.

```php
// correcto: datos ordenados por categoria
$sql = 'SELECT categoria, nombre, importe FROM ventas ORDER BY categoria, nombre';
```

Si los datos no están ordenados, la misma categoría puede aparecer en varios bloques no consecutivos, generando subtotales incorrectos.


## 📊 Totales globales vs. subtotales

El `<footer>` del **grupo exterior** acumula los totales de todas las filas de todos los subgrupos: es el total global. El `<footer>` del **subgrupo** acumula solo las filas de ese bloque y se reinicia en cada ruptura.

Ambos pueden coexistir: el exterior muestra el gran total y el interior muestra el subtotal por categoría.


## 📄 Salto de página en cada ruptura

Por defecto, una ruptura no fuerza un salto de página: el siguiente bloque continúa en la misma página si hay espacio. Para que **cada subgrupo empiece en una página nueva**:

```php
$template->setRenderCfgValue('pageBreakOnRupture', true);
```

Ver **[32 - Configuración de render](32-configuracion-render.md)**.
