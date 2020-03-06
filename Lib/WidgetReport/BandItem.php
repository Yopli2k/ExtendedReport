<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

/**
 * Description of BandItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandItem
{

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
     * @param array $data
     */
    public function __construct($data)
    {
        $this->height = isset($data['height']) ? (int) $data['height'] : 0;
        $this->loadColumns($data['children']);
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
