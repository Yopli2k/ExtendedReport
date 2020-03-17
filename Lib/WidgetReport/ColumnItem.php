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
 * Description of ColumnItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ColumnItem
{

    /**
     *
     * @var int
     */
    public $posx;

    /**
     *
     * @var int
     */
    public $posy;

    /**
     * Display object configuration
     */
    public $widget;

    /**
     *
     * @var int
     */
    public $width;

    /**
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->posx = isset($data['posx']) ? (int) $data['posx'] : 0;
        $this->posy = isset($data['posy']) ? (int) $data['posy'] : 0;
        $this->width = isset($data['width']) ? (int) $data['width'] : 0;
        $this->loadWidget($data['children']);
    }

    /**
     * Add column to the PDF file.
     *
     * @param Cezpdf $pdf
     * @param object $data
     * @param float  $linePos
     */
    public function render(&$pdf, &$data, $linePos)
    {
        $posY = $linePos - $this->posy;
        $this->widget->setValue($data);
        $this->widget->render($pdf, $this->posx, $posY, $this->width);
    }

    /**
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
