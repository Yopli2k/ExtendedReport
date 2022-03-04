<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

/**
 * Specific band for the report footer data.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandFooter extends BandItem
{

    /**
     *
     * @var bool
     */
    public $forceNewPage;

    /**
     *
     * @var bool
     */
    public $placeBottom;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->forceNewPage = $data['newpage'] ?? false;
        $this->placeBottom = $data['placebottom'] ?? false;
    }

    /**
     *
     *
     * @param Object $data
     */
    public function calculate(&$data)
    {
        foreach ($this->columns as $column) {
            if (method_exists($column->widget, 'process')) {
                $column->widget->process($data);
            }
        }
    }
}
