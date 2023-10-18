<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2023 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use FacturaScripts\Core\Base\ToolBox;

/**
 * Base class for displaying data in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class WidgetItem
{

    /**
     * The color has to use when representing the data.
     *
     * @var array
     */
    protected $color;

    /**
     * Name of the field from which the value to be represented is obtained.
     *
     * @var string
     */
    protected $fieldname;

    /**
     * Value to be represented.
     *
     * @var string
     */
    protected $value;

    /**
     * Widget type.
     *
     * @var string
     */
    protected $type;

    /**
     * Add object to the PDF file.
     */
    abstract public function render(&$pdf, $posX, $posY, $width, $height);

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->type = $data['type'];
        $this->fieldname = isset($data['fieldname']) ? $data['fieldname'] : '';
        $this->value = isset($data['value']) ? $data['value'] : '';

        $color = isset($data['color']) ? $data['color'] : 'black';
        $this->color = $this->rgbFromColor($color);
    }

    /**
     * Get the widget type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Convert color name to rgb values. Range value 0 -> 1.
     * See https://es.wikipedia.org/wiki/Colores_web?section=6#Tabla_de_colores
     *
     * @param string $color
     * @return array
     */
    public function rgbFromColor(string $color): array
    {
        switch ($color) {
            case 'black':
                return ['r' => 0/255, 'g' => 0/255, 'b' => 0/255];

            case 'blue':
                return ['r' => 0/255, 'g' => 0/255, 'b' => 255/255];

            case 'green':
                return ['r' => 0/255, 'g' => 128/255, 'b' => 0/255];

            case 'orange':
                return ['r' => 255/255, 'g' => 165/255, 'b' => 0/255];

            case 'red':
                return ['r' => 255/255, 'g' => 0/255, 'b' => 0/255];

            case 'white':
                return ['r' => 255/255, 'g' => 255/255, 'b' => 255/255];

            case 'yellow':
                return ['r' => 255/255, 'g' => 255/255, 'b' => 0/255];

            case 'silver':
                return ['r' => 240/255, 'g' => 240/255, 'b' => 240/255];

            default:
                return $this->colorFromHex($color);
        }
    }

    /**
     * Convert hex color representation to to rgb values. Range value 0 -> 1.
     * See https://es.wikipedia.org/wiki/Colores_web?section=6#Tabla_de_colores
     *
     * @param string $color
     * @return array
     */
    public function colorFromHex(string $color): array
    {
        if (substr($color, 0, 1) == '#') {
            $color = substr($color, 1);
        }

        $red = substr($color, 0, 2) ?? '255';
        $green = substr($color, 2, 2) ?? '255';
        $blue = substr($color, 4, 2) ?? '255';
        return ['r' => hexdec($red)/255, 'g' => hexdec($green)/255, 'b' => hexdec($blue)/255];
    }

    /**
     * Set value from dataset to widget if fieldname is not empty.
     *
     * @param object $data
     */
    public function setValue(&$data)
    {
        if (false === empty($this->fieldname)) {
            $this->value =  $this->getValueForFieldName($data);
        }
    }

    /**
     * Get the value of the fieldname from the data object.
     *
     * @param object $data
     * @return mixed|string
     */
    protected function getValueForFieldName(&$data)
    {
        if (empty($this->fieldname) || false === isset($data)) {
            return '';
        }

        // if fieldname is an array
        $pos = strpos($this->fieldname, '[');
        if (false !== $pos) {
            $len = strpos($this->fieldname, ']', $pos) - $pos - 1;
            $index = substr($this->fieldname, $pos + 1, $len);
            $fieldname = substr($this->fieldname, 0, $pos);
            return $data->{$fieldname}[$index] ?? '';
        }

        // if fieldname is a function, exec funtion for get the value
        $pos = strpos($this->fieldname, '(');
        if (false !== $pos) {
            $functionName = substr($this->fieldname, 0, $pos);
            if (method_exists($data, $functionName)) {
                $paramsStr = str_replace(["'", " "], '', substr($this->fieldname, $pos + 1, -1));
                $params = explode(',', $paramsStr);
                return false === $params
                    ? $data->{$functionName}()
                    : $data->{$functionName}($params);
            }
            return '';
        }

        // fieldname is a field from data
        return $data->{$this->fieldname} ?? '';
    }

    /**
     * Class with common tools.
     *
     * @return ToolBox
     */
    protected static function toolBox()
    {
        return new ToolBox();
    }
}
