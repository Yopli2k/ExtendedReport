<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFCreator;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\BandItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ColumnItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ConfigItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ReportItemLoadEngine;
use FacturaScripts\Plugins\ExtendedReport\Model\Base\ModelReport;

/**
 * Description of PDFTemplate
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class PDFTemplate
{

    const DATASET_HEADER = 'header';
    const DATASET_DETAIL = 'detail';
    const DATASET_FOOTER = 'footer';

    /**
     * Template structure
     *
     * @var GroupItem[]
     */
    public $groups;

    /**
     * Template configuration
     *
     * @var ConfigItem
     */
    public $config;

    /**
     * Name of the template report.
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var PDFCreator
     */
    protected $engine;

    /**
     *
     * @var ModelReport[]
     */
    private $datasets = [];

    /**
     *
     * @var int
     */
    private $pageNum;

    /**
     * Class constructor. Load XML template structure.
     *
     * @param string      $name
     */
    public function __construct($name)
    {
        if (ReportItemLoadEngine::installXML($name, $this) === false) {
            return;
        }

        $this->engine = new PDFCreator(
            $this->config->page['type'],
            $this->config->page['orientation']
        );
    }

    /**
     *
     * @param string $name
     * @param string $band
     * @param ModelReport $model
     */
    public function addDataset($name, $band, $model)
    {
        $this->datasets[$name][$band] = $model;
    }

    public function render()
    {
        $this->pageNum = 1;

        $mainName = $this->getMainNameGroup();
        $this->renderHeader($mainName, 0.00);
    }

    /**
     *
     * @param string $name
     * @param float  $position
     */
    private function renderHeader($name, $position)
    {
        $group = $this->groups[$name];
        $model = $this->datasets[$name][self::DATASET_HEADER] ?? null;
        $this->renderBand($group->header, $model, $position);
    }

    /**
     *
     * @return string
     */
    private function getMainNameGroup()
    {
        return empty($this->config->default['group'])
            ? (string) array_key_first($this->groups)
            : $this->config->default['group'];
    }

    /**
     *
     * @param BandItem    $band
     * @param ModelReport $model
     * @param float       $linePos
     */
    private function renderBand($band, $model, $linePos)
    {
        foreach ($band->columns as $column) {
            $this->renderColumn($column, $model, $linePos);
        }
    }

    /**
     *
     * @param ColumnItem  $column
     * @param ModelReport $model
     * @param float       $linePos
     */
    private function renderColumn($column, $model, $linePos)
    {

    }
}
