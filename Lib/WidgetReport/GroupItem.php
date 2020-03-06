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
 * Description of GroupItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class GroupItem
{

    const BAND_DETAIL = 'detail';
    const BAND_FOOTER = 'footer';
    const BAND_HEADER = 'header';

    /**
     *
     * @var BandItem[]
     */
    public $detail = [];

    /**
     *
     * @var BandItem[]
     */
    public $footer = [];

    /**
     *
     * @var BandItem[]
     */
    public $header = [];

    /**
     * Name identificator
     *
     * @var string
     */
    public $name;

    /**
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->name = $data['name'] ?? '';
        $this->loadBands($data['children']);
    }

    /**
     * Get the list of band names.
     *
     * @return array
     */
    protected function getBandList()
    {
        return [ self::BAND_HEADER, self::BAND_DETAIL, self::BAND_FOOTER ];
    }

    /**
     *
     * @param array $children
     */
    protected function loadBands($children)
    {
        $bandClass = ReportItemLoadEngine::getNamespace() . 'BandItem';
        foreach ($children as $child) {
            $type = $child['tag'];
            if (\in_array($type, $this->getBandList())) {
                $bandItem = new $bandClass($child);
                $this->{$type} = $bandItem;
            }
        }
    }
}
