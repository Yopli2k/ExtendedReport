<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
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
    abstract function render(&$pdf, $posX, $posY, $width);

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
        $this->color = $this->rgbFromColorName($color);
    }

    /**
     * Get the widget type
     *
     * @return string
     */
    public function getType()
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
    public function rgbFromColorName(string $color): array
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
                return ['r' => 0/255, 'g' => 0/255, 'b' => 0/255]; // Black
        }
    }

    /**
     * Set value from dataset to widget
     *
     * @param object $data
     */
    public function setValue(&$data)
    {
        if (!empty($this->fieldname) && isset($data)) {
            $this->value = @$data->{$this->fieldname};
        }
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
