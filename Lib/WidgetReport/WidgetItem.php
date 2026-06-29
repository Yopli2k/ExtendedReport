<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2026 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2026 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public license as
 * published by the Free Software Foundation, either version 3 of the
 * license, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public license for more details.
 *
 * You should have received a copy of the GNU Lesser General Public license
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use Cezpdf;

/**
 * Base class for displaying data in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class WidgetItem
{
    /**
     * Bootstrap contextual color (success, info, warning...) for the summary card
     * built from this widget when its column is placed in the 'cards' area. Empty
     * means the render engine assigns a pastel from its rotating palette. HTML only.
     *
     * @var string
     */
    protected string $cardColor;

    /**
     * The colour has to use when representing the data.
     *
     * @var array
     */
    protected array $color;

    /**
     * Extra CSS classes for the HTML output, declared in the XML 'class' attribute.
     * Ignored by the PDF. Lets the report author apply Bootstrap utilities (e.g. h2, fs-4).
     *
     * @var string
     */
    protected string $cssClass;

    /**
     * The Name of the field from which the value to be represented is obtained.
     *
     * @var string
     */
    protected string $fieldname;

    /**
     * Caption shown as the title of the summary card built from this widget when
     * its column is placed in the 'cards' area. Translated by the render engine.
     * HTML only; the PDF ignores it.
     *
     * @var string
     */
    protected string $title;

    /**
     * Widget type.
     *
     * @var string
     */
    protected string $type;

    /**
     * Value to be represented.
     *
     * @var string
     */
    protected string $value;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->cardColor = $data['cardcolor'] ?? '';
        $this->cssClass = $data['class'] ?? '';
        $this->fieldname = $data['fieldname'] ?? '';
        $this->title = $data['title'] ?? '';
        $this->value = $data['value'] ?? '';

        $color = $data['color'] ?? 'black';
        $this->color = $this->rgbFromColor($color);
    }

    /**
     * Convert hex color representation to rgb values. Range value 0 -> 1.
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
     * Convert an internal rgb array (range 0 -> 1) to a CSS hex color string.
     * Returns an empty string when the array is empty.
     *
     * @param array $rgb
     * @return string
     */
    public function cssColor(array $rgb): string
    {
        if (empty($rgb)) {
            return '';
        }

        return sprintf(
            '#%02x%02x%02x',
            (int) round(($rgb['r'] ?? 0) * 255),
            (int) round(($rgb['g'] ?? 0) * 255),
            (int) round(($rgb['b'] ?? 0) * 255)
        );
    }

    /**
     * Get the field name associated to widget.
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldname;
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
     * Get the value to be represented.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Add an object to the PDF file.
     */
    abstract public function render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height);

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
                return ['r' => 0/255, 'g' => 0/255, 'b' => 1];

            case 'green':
                return ['r' => 0/255, 'g' => 128/255, 'b' => 0/255];

            case 'orange':
                return ['r' => 1, 'g' => 165/255, 'b' => 0/255];

            case 'red':
                return ['r' => 1, 'g' => 0/255, 'b' => 0/255];

            case 'white':
                return ['r' => 1, 'g' => 1, 'b' => 1];

            case 'yellow':
                return ['r' => 1, 'g' => 1, 'b' => 0/255];

            case 'silver':
                return ['r' => 240/255, 'g' => 240/255, 'b' => 240/255];

            default:
                return $this->colorFromHex($color);
        }
    }

    /**
     * Set value from dataset to widget if fieldname is not empty.
     *
     * @param Object $data
     */
    public function setValue(Object $data): void
    {
        if (false === empty($this->fieldname)) {
            $this->value =  $this->getValueForFieldName($data);
        }
    }

    /**
     * Return the widget data in a neutral structure, ready to be rendered as HTML.
     * The render engine (band + Twig) decides the final markup. The value is the
     * already-formatted text (number/currency/translate); the visual style is
     * expressed as CSS classes and inline styles, never injected into the value.
     *
     * Contract:
     *   - tag:   semantic hint of the element ('span', 'img', 'hr'...).
     *   - value: text to print (raw, the template is responsible for escaping).
     *   - class: space separated css classes.
     *   - style: inline css declarations separated by ';'.
     *
     * @return array
     */
    public function toHtmlData(): array
    {
        return [
            'tag' => 'span',
            'value' => $this->getValue(),
            'class' => $this->cssClass,
            'style' => '',
            'title' => $this->title,
            'cardcolor' => $this->cardColor,
        ];
    }

    /**
     * Get the value of the fieldname from the data object.
     *
     * @param ?Object $data
     * @return string
     */
    protected function getValueForFieldName(?Object $data): string
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

        // if fieldname is a property from an object, get the value from the object
        $pos = strpos($this->fieldname, '.');
        if (false !== $pos) {
            $objectName = substr($this->fieldname, 0, $pos);
            $property = substr($this->fieldname, $pos + 1);
            $object = $data->{$objectName} ?? null;
            return isset($object) && isset($object->{$property})
                ? $object->{$property}
                : '';
        }

        // fieldname is a field from data
        return $data->{$this->fieldname} ?? '';
    }
}
