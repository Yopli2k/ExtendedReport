La etiqueta `<config>` define la configuración global del informe: el tamaño y orientación de página, la fuente de texto y el grupo por defecto. Es opcional; si se omite, el motor aplica valores por defecto.

```xml
<config>
    <page type="A4" orientation="portrait" />
    <font type="Arial" size="12" />
    <default group="main" alter="other" />
</config>
```


## 📄 Etiqueta `page`

Define el tamaño y la orientación del papel.

Atributos:
- **`type`**: tamaño de página. Por defecto `a4`. Acepta los formatos de la librería Cezpdf: `A4`, `Letter`, `Legal`, etc.
- **`orientation`**: orientación de la página. Por defecto `portrait`. Valores: `portrait` (vertical) o `landscape` (horizontal).

Ejemplos:

```xml
<!-- A4 vertical -->
<page type="A4" orientation="portrait" />

<!-- A4 horizontal -->
<page type="A4" orientation="landscape" />
```

El tamaño y orientación determinan el ancho y alto útil de la página, que el motor usa para calcular posiciones y decidir los saltos de página automáticos.


## 🔤 Etiqueta `font`

Define la fuente de texto por defecto.

Atributos:
- **`type`**: nombre de la fuente. Por defecto `Helvetica`.
- **`size`**: tamaño base de la fuente. Por defecto `12`.

> **Nota**: actualmente la fuente queda forzada a `Helvetica` internamente. El atributo `type` queda reservado para cuando se añada soporte de fuentes adicionales (TTF). El `size` sí se aplica como tamaño base.


## 🎯 Etiqueta `default`

Indica cuál de los grupos del informe se usa como referencia principal.

Atributos:
- **`group`**: nombre del grupo principal. Por defecto `main`. Si un grupo no tiene dataset asociado por nombre, usará el dataset del grupo indicado aquí.
- **`alter`**: identificador alternativo. Reservado para uso interno; puedes ignorarlo o no incluirlo.

Ejemplo con dos grupos donde el principal es `ventas`:

```xml
<default group="ventas" />
```
