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

use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\BandItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\BandDetail;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\BandHeader;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\BandFooter;

/**
 * Class to handle a group of bands (header, detail and footer) of the report.
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
     * Structure with detail columns.
     *
     * @var BandDetail|GroupItem
     */
    public $detail;

    /**
     * Structure with footers columns.
     *
     * @var BandFooter[]
     */
    public $footer = [];

    /**
     * Structure with headers columns.
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
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->name = $data['name'] ?? '';
        $this->loadBands($data['children']);
    }

    /**
     * Gets the band defined as detail.
     *
     * @return BandDetail
     */
    public function getDetail()
    {
        if ($this->detail instanceof GroupItem) {
            return $this->detail->getDetail();
        }

        return $this->detail;
    }

    /**
     * Gets the band defined as header.
     *
     * @param bool $second
     * @return BandHeader|null
     */
    public function getHeader($second)
    {
        return $this->getBand($this->header, $second);
    }

    /**
     * Gets the height of the headers from the indicated group
     * to the last header.
     *
     * @param bool $second
     * @return int
     */
    public function getHeaderHeight($second)
    {
        $header = $this->getHeader($second);
        $height = $header->height;
        if ($this->detail instanceof GroupItem) {
            $height += $this->detail->getHeaderHeight($second);
        }

        return $height;
    }

    /**
     * Gets the band defined as footer.
     *
     * @param bool $second
     * @return BandFooter|null
     */
    public function getFooter($second)
    {
        return $this->getBand($this->footer, $second);
    }

    /**
     * Get the height of the indicated footer.
     *
     * @param bool $second
     * @return int
     */
    public function getFooterHeight($second)
    {
        $footer = $this->getFooter($second);
        return isset($footer) ? $footer->height : 0;
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
     * Create the band structure from an array.
     *
     * @param array $children
     */
    protected function loadBands($children)
    {
        foreach ($children as $child) {
            $type = $child['tag'];
            switch ($type) {
                case self::BAND_DETAIL:
                    $this->detail = $this->createBand($type, $child);
                    break;

                case self::BAND_HEADER:
                case self::BAND_FOOTER:
                    $band = $this->createBand($type, $child);
                    $this->{$type}[$band->type] = $band;
                    break;

                case self::BAND_GROUP:
                    $this->detail = new self($child);
                    break;
            }
        }
    }

    /**
     * Create and return a band of the indicated type.
     *
     * @param string $type
     * @param array $data
     * @return BandItem
     */
    private function createBand($type, $data)
    {
        $className = ReportItemLoadEngine::getNamespace() . 'Band' . ucfirst($type);
        return new $className($data);
    }

    /**
     *
     * @param BandItem[] $bands
     * @param bool       $second
     * @return BandItem
     */
    private function getBand($bands, $second)
    {
        $result = null;
        if ($second) {
            $result = $bands[BandItem::BAND_TYPE_SECOND] ?? null;
        }

        if ($result == null) {
            $result = $bands[BandItem::BAND_TYPE_MAIN] ?? null;
        }

        return $result;
    }
}
