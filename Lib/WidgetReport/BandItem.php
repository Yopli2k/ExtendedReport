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

/**
 * General class of the different data bands.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class BandItem
{

    const BAND_TYPE_MAIN = 'main';
    const BAND_TYPE_SECOND = 'second';

    /**
     * List of columns to display in the report.
     *
     * @var ColumnItem[]
     */
    public $columns = [];

    /**
     * Band height.
     *
     * @var int
     */
    public $height;

    /**
     * Indicates the type of band.
     *
     * @var string
     */
    public $type;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->height = isset($data['height']) ? (int) $data['height'] : 0;
        $this->type = isset($data['type']) ? $data['type'] : self::BAND_TYPE_MAIN;
        $this->loadColumns($data['children']);
    }

    /**
     * Add all objects in a band to the PDF file.
     *
     * @param Cezpdf $pdf
     * @param ReportDefaultData $default
     * @param object $data
     * @param float  $linePos
     */
    public function render($pdf, $default, $data, $linePos)
    {
        foreach ($this->columns as $column) {
            $column->render($pdf, $default, $data, $linePos);
        }
    }

    /**
     * Create the column structure with the array data.
     *
     * @param array $children
     */
    protected function loadColumns($children)
    {
        $columnClass = ReportItemLoadEngine::getNamespace() . 'ColumnItem';
        foreach ($children as $child) {
            if ($child['tag'] !== 'column') {
                continue;
            }

            $columnItem = new $columnClass($child);
            $this->columns[] = $columnItem;
        }
    }
}
