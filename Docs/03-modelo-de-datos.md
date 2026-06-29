El modelo de datos es la pieza PHP que obtiene y prepara las filas que el informe va a representar. ExtendedReport define la clase base abstracta **`ModelReport`**, que debes extender en tu plugin para cada informe que crees.


## 📁 Dónde colocar el modelo

Los modelos de informe se guardan en una subcarpeta `Report` dentro de la carpeta `Model` de tu plugin. Si cada fila del informe necesita una clase propia para almacenar sus datos calculados, se recomienda separarla en una subcarpeta `Data`:

```
TuPlugin/
└── Model/
    └── Report/
        ├── MiInforme.php          ← el modelo del informe
        └── Data/
            └── MiInformeData.php  ← la clase de cada fila (opcional)
```

Esta separación es especialmente útil cuando los datos requieren cálculos complejos antes de mostrarse, manteniendo el código organizado.


## 🧩 La clase ModelReport

`ModelReport` es una clase abstracta con dos miembros que debes conocer:

- **`$data`**: array público donde debes almacenar las filas del informe. Cada elemento es un objeto (o `stdClass`) cuyas propiedades son los campos que luego referenciarás en el XML con `fieldname`.
- **`$dataBase`**: instancia estática de `DataBase`, disponible para lanzar consultas SQL directamente.
- **`loadData()`**: método abstracto que debes implementar obligatoriamente. Es el encargado de obtener los datos y rellenar `$data`.


## ✏️ Ejemplo básico

```php
namespace FacturaScripts\Plugins\TuPlugin\Model\Report;

use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\ModelReport;

class ClientesReport extends ModelReport
{
    public function loadData(): void
    {
        $sql = 'SELECT codcliente, nombre, email FROM clientes ORDER BY nombre';
        foreach (self::$dataBase->select($sql) as $row) {
            $item = new \stdClass();
            $item->codigo = $row['codcliente'];
            $item->nombre = $row['nombre'];
            $item->email  = $row['email'];
            $this->data[] = $item;
        }
    }
}
```

En el XML usarás `fieldname="codigo"`, `fieldname="nombre"` y `fieldname="email"` para mostrar estos valores.


## ✏️ Ejemplo con clase de datos separada

Cuando cada fila contiene cálculos o una estructura más compleja, es más limpio definir una clase aparte para la fila:

```php
// Model/Report/Data/ComparativaData.php
class ComparativaData
{
    public string $codigo  = '';
    public string $nombre  = '';
    public float  $actual  = 0.0;
    public float  $anterior = 0.0;

    public function diferencia(): float
    {
        return $this->actual - $this->anterior;
    }
}
```

```php
// Model/Report/ComparativaReport.php
class ComparativaReport extends ModelReport
{
    public function loadData(): void
    {
        foreach (self::$dataBase->select($this->getSQL()) as $row) {
            $item = new ComparativaData();
            $item->codigo   = $row['codigo'];
            $item->nombre   = $row['nombre'];
            $item->actual   = (float) $row['actual'];
            $item->anterior = (float) $row['anterior'];
            $this->data[] = $item;
        }
    }

    private function getSQL(): string
    {
        return 'SELECT ...';
    }
}
```

En el XML puedes usar `fieldname="diferencia()"` para invocar el método y mostrar su resultado.


## 📌 Notas importantes

- El método `loadData()` no recibe parámetros por defecto. Si necesitas filtros (fechas, cliente, etc.), añade propiedades públicas al modelo y asígnalas antes de llamar a `loadData()`:

```php
$model = new ComparativaReport();
$model->fechaDesde = '2025-01-01';
$model->fechaHasta = '2025-12-31';
$model->loadData();
```

- El array `$data` debe contener los registros en el orden en que quieres que aparezcan en el informe. Si usas rupturas de subgrupo, **el orden es obligatorio**: los datos deben estar ordenados por el campo de ruptura (ver **[20 - Ruptura de subgrupos](20-ruptura-subgrupos.md)**).

- `$dataBase` está disponible para consultas SQL directas, pero también puedes usar los modelos de FacturaScripts (`Where`, `all()`, `select()`) para obtener los datos si lo prefieres.
