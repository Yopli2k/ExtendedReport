ExtendedReport incluye dos widgets para insertar imágenes en el informe:

- **`image`**: inserta una imagen cuya ruta se indica directamente.
- **`imageproduct`**: variante de `image` que localiza automáticamente la imagen de un producto a partir de su referencia.


## 🖼️ Widget `image`

Inserta una imagen (JPG, PNG o GIF) en la posición de la columna. El tamaño de dibujo lo determinan el `width` y el `height` de la columna.

```xml
<column posx="20" posy="20" width="80" height="80">
    <widget type="image" value="/ruta/a/la/imagen.png" resize="true" />
</column>
```

Atributos específicos:
- **`value`** o **`fieldname`**: ruta del archivo de imagen. Con `value` se usa una ruta fija; con `fieldname` se obtiene la ruta del campo del registro.
- **`resize`**: si es `true`, genera una miniatura ajustada al `width`/`height` de la columna y la cachea en `MyFiles/Tmp/Thumbnails/`. Por defecto `false`.
- **`align`**: alineación de la imagen dentro de la celda. Por defecto `center`.
- **`angle`**: rotación en grados (0–360). Por defecto `0`.
- **`padding`**: margen interior. Por defecto `5`.


## 📦 Widget `imageproduct`

Variante de `image` que, a partir de la **referencia** de un producto (en `value` o `fieldname`), localiza su imagen asociada y la pinta. Muy útil para catálogos o fichas con foto del artículo.

```xml
<column posx="20" posy="20" width="80" height="80">
    <widget type="imageproduct" fieldname="referencia" resize="true" />
</column>
```

Hereda todos los atributos de `image`. Internamente:
- Busca la variante del producto por su `referencia`.
- Obtiene la imagen asociada (prioriza la imagen específica de esa referencia; si no existe, usa la imagen general del producto).
- Si no encuentra ninguna imagen o archivo, no pinta nada (falla en silencio).


## 📌 Ejemplos

Logo de la empresa en la cabecera (ruta fija):
```xml
<column posx="20" posy="10" width="120" height="60">
    <widget type="image" value="/Plugins/TuPlugin/Assets/Images/logo.png"
            resize="true" align="left" />
</column>
```

Imagen del producto en el detalle (obtenida de la referencia del registro):
```xml
<column posx="20" posy="5" width="60" height="60">
    <widget type="imageproduct" fieldname="referencia" resize="true" align="center" />
</column>
```

Imagen desde campo del modelo (ruta dinámica por registro):
```xml
<column posx="20" posy="5" width="80" height="80">
    <widget type="image" fieldname="rutaImagen" resize="true" />
</column>
```
