El widget `number` muestra valores numéricos formateados: separadores de miles y decimales según la configuración global de FacturaScripts, opción de símbolo de divisa, iconos a izquierda y derecha, y color diferente para los valores negativos.

Hereda de `label`, por lo que admite todos sus atributos de estilo. La diferencia principal es que la alineación por defecto es `right` en lugar de `left`.

```xml
<widget type="number" fieldname="importe" />
```


## ⚙️ Atributos específicos

- **`decimal`**: número de decimales a mostrar. Si no se indica, usa los decimales configurados en FacturaScripts (`default.decimals`, habitualmente 2).
- **`printempty`**: controla qué hacer cuando el valor es 0 o está vacío.
    - `true` (por defecto): siempre imprime el número, incluso si es 0.
    - `false`: no imprime nada si el valor es 0 o vacío. Útil para listas donde los ceros son ruido visual.
- **`currency`**: nombre del **campo** del registro de datos que contiene el código de divisa (por ejemplo `"coddivisa"`). Si se indica, añade el símbolo de la divisa según la configuración global (`default.currency_position`).
- **`licon`**: texto o símbolo que se añade a la **izquierda** del valor.
- **`ricon`**: texto o símbolo que se añade a la **derecha** del valor.
- **`negative`**: color del texto cuando el valor es negativo. Acepta los mismos valores que `color` (nombre o hexadecimal). Si no se indica, usa el mismo color que el resto del texto.

Los separadores de miles y decimales se toman de la configuración global (`default.thousands_separator` y `default.decimal_separator`).


## 📌 Ejemplos

Importe básico con 2 decimales alineado a la derecha:
```xml
<column posx="460" width="80">
    <widget type="number" fieldname="importe" />
</column>
```

Cantidad sin decimales:
```xml
<column posx="200" width="50">
    <widget type="number" fieldname="unidades" decimal="0" />
</column>
```

Importe con símbolo de divisa:
```xml
<column posx="460" width="90">
    <widget type="number" fieldname="total" currency="coddivisa" />
</column>
```

Porcentaje a dos decimales con símbolo a la derecha y rojo para negativos:
```xml
<column posx="400" width="50">
    <widget type="number" fieldname="variacion" decimal="2" ricon="%" negative="red" />
</column>
```

Primer elemento de un array sin imprimir ceros:
```xml
<column posx="180" width="80">
    <widget type="number" fieldname="valores[0]" decimal="2" printempty="false" />
</column>
```

Valor devuelto por un método del modelo:
```xml
<column posx="320" width="80">
    <widget type="number" fieldname="getTotal()" decimal="2" bold="true" />
</column>
```
