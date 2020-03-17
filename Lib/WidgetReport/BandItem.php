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
 * Description of BandItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class BandItem
{

    const BAND_TYPE_MAIN = 'main';
    const BAND_TYPE_SECOND = 'second';

    /**
     *
     * @var ColumnItem[]
     */
    public $columns = [];

    /**
     *
     * @var int
     */
    public $height;

    /**
     *
     * @var string
     */
    public $type;

    /**
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
     * @param object $data
     * @param float  $linePos
     */
    public function render(&$pdf, $data, $linePos)
    {
        foreach ($this->columns as $column) {
            $column->render($pdf, $data, $linePos);
        }
    }

    /**
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
