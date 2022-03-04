<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
 * Class for displaying one line in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetLine extends WidgetItem
{

    /**
     * Line height.
     *
     * @var int
     */
    protected $height;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->height = isset($data['height']) ? (int) $data['height'] : 1;
    }

    /**
     * Add Label to pdf document.
     *
     * @param Cezpdf $pdf
     * @param float $posX
     * @param float $posY
     * @param float $width
     */
    public function render(&$pdf, $posX, $posY, $width)
    {
        $pdf->setLineStyle($this->height);
        $pdf->setStrokeColor($this->color['r'], $this->color['g'], $this->color['b']);
        $pdf->line($posX, $posY, ($posX + $width), $posY);
    }
}
