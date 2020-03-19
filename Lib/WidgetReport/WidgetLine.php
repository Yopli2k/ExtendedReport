<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
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
