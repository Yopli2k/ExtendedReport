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

use Cezpdf;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ReportDefaultData;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\WidgetDefault;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\WidgetItem;

/**
 * Class to manage the data columns of the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ColumnItem
{

    public $height;

    /**
     * Position on x-axis.
     *
     * @var int
     */
    public $posx;

    /**
     * Position on the y-axis.
     *
     * @var int
     */
    public $posy;

    /**
     * Display object configuration
     *
     * @var WidgetItem
     */
    public $widget;

    /**
     * Column width.
     *
     * @var int
     */
    public $width;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->posx = isset($data['posx']) ? (int) $data['posx'] : 0;
        $this->posy = isset($data['posy']) ? (int) $data['posy'] : 0;
        $this->width = isset($data['width']) ? (int) $data['width'] : 30;
        $this->height = isset($data['height']) ? (int) $data['height'] : 15;
        $this->loadWidget($data['children']);
    }

    /**
     * Add column to the PDF file.
     *
     * @param Cezpdf $pdf
     * @param ReportDefaultData $default
     * @param object $data
     * @param float  $linePos
     */
    public function render($pdf, $default, $data, $linePos)
    {
        $posY = $linePos - $this->posy;
        $values = ($this->widget instanceof WidgetDefault) ? $default : $data;
        $this->widget->setValue($values);
        $this->widget->render($pdf, $this->posx, $posY, $this->width, $this->height);
    }

    /**
     * Create the visual structure for each column.
     *
     * @param array $children
     */
    protected function loadWidget($children)
    {
        foreach ($children as $child) {
            if ($child['tag'] !== 'widget') {
                continue;
            }

            $className = ReportItemLoadEngine::getNamespace() . 'Widget' . ucfirst($child['type']);
            if (class_exists($className)) {
                $this->widget = new $className($child);
            }
            break;
        }
    }
}
