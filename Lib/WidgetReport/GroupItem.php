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

    private const BAND_DETAIL = 'detail';
    private const BAND_FOOTER = 'footer';
    private const BAND_HEADER = 'header';
    private const BAND_GROUP  = 'group';

    /**
     *
     * @var BandDetail
     */
    public $detail;

    /**
     *
     * @var BandFooter[]
     */
    public $footer = [];

    /**
     *
     * @var GroupItem[]
     */
    public $groups = [];

    /**
     *
     * @var BandHeader[]
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
        foreach ($children as $child) {
            $type = $child['tag'];
            switch ($type) {
                case self::BAND_DETAIL:
                    $this->detail = $this->getBand($type, $child);
                    break;

                case self::BAND_HEADER:
                case self::BAND_FOOTER:
                    $band = $this->getBand($type, $child);
                    $this->{$type}[$band->type] = $band;
                    break;

                case self::BAND_GROUP:
                    $group = new self($child);
                    $this->groups[$group->name] = $group;
                    break;
            }
        }
    }

    /**
     *
     * @param string $type
     * @param array $data
     * @return BandItem
     */
    private function getBand($type, $data)
    {
        $className = ReportItemLoadEngine::getNamespace() . 'Band' . ucfirst($type);
        return new $className($data);
    }
}
