<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2024 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
 * Class for displaying one line in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetLine extends WidgetItem
{

    /**
     * The color for background data.
     *
     * @var array
     */
    protected array $bgcolor;

    /**
     * Line border. Sets the thickness of the line.
     * Default 1.
     *
     *
     * @var int
     */
    protected $border;

    /**
     * Line height.
     * If a value is defined, the height of the box is set.
     * Default 1.
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
        $this->border = isset($data['border']) ? (int) $data['border'] : 1;
        $this->height = isset($data['height']) ? (int) $data['height'] : 0;

        $color = $data['bgcolor'] ?? false;
        $this->bgcolor = $color ? $this->rgbFromColor($color) : [];
    }

    /**
     * Add a Line or rectangle to PDF document.
     * If the height is greater than twice the border, a rectangle is drawn.
     *
     * @param Cezpdf $pdf
     * @param float $posX
     * @param float $posY
     * @param float $width
     */
    public function render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height)
    {
        // Is a rectangle
        if ($this->height > ($this->border * 2)) {
            if (false === empty($this->bgcolor)) {
                $pdf->setColor($this->bgcolor['r'], $this->bgcolor['g'], $this->bgcolor['b']);
                $pdf->filledRectangle($posX, ($posY - $this->height), $width, $this->height);
            }
            if ($this->border > 0) {
                $pdf->setLineStyle($this->border);
                $pdf->setStrokeColor($this->color['r'], $this->color['g'], $this->color['b']);
                $pdf->rectangle($posX, ($posY - $this->height), $width, $this->height);
            }
            return;
        }

        // Is a line
        $pdf->setLineStyle($this->border);
        $pdf->setStrokeColor($this->color['r'], $this->color['g'], $this->color['b']);
        $pdf->line($posX, $posY, ($posX + $width), $posY);
    }
}
