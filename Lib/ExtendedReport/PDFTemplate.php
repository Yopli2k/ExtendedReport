<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use Cezpdf;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;
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

    // const PAGE_TYPE_DEFAULT = 'A4';
    // const PAGE_ORIENTATION_H = 'landscape';
    // const PAGE_ORIENTATION_V = 'portrait';

    const DATASET_HEADER = 'header';
    const DATASET_DETAIL = 'detail';
    const DATASET_FOOTER = 'footer';

    /**
     * Name of the template report.
     *
     * @var string
     */
    public $name;

    /**
     * Template configuration
     *
     * @var ConfigItem
     */
    public $config;

    /**
     * Template structure
     *
     * @var GroupItem[]
     */
    public $groups;

    /**
     * List of models with the data.
     *
     * @var ModelReport[]
     */
    protected $datasets = [];

    /**
     *
     * @var int
     */
    protected $pageHeight;

    /**
     * Current Page Number
     *
     * @var int
     */
    protected $pageNum;

    /**
     *
     * @var int
     */
    protected $pageWidth;

    /**
     * PDF object.
     *
     * @var Cezpdf
     */
    protected $pdf;

    /**
     * Add source data for the band named.
     *
     * @param string $name
     * @param string $band
     * @param ModelReport $model
     */
    public function addDataset(string $name, string $band, $model)
    {
        $this->datasets[$name][$band] = $model;
    }

    /**
     * Load XML template structure.
     *
     * @param string $name
     * @return bool
     */
    public function loadTemplate(string $name): bool
    {
        if (ReportItemLoadEngine::installXML($name, $this) === false) {
            return false;
        }

        $this->pdf = new Cezpdf($this->config->page['type'], $this->config->page['orientation']);
        $this->pdf->addInfo('Creator', 'FacturaScripts');
        $this->pdf->addInfo('Producer', 'FacturaScripts');
        $this->pdf->tempPath = \FS_FOLDER . '/MyFiles/Cache';
        $this->pageWidth = $this->pdf->ez['pageWidth'];
        $this->pageHeight = $this->pdf->ez['pageHeight'];
        return true;
    }

    /**
     * Create the PDF file according to the template loaded.
     *
     * @return string
     */
    public function render()
    {
        if (!isset($this->pdf)) {
            return;
        }

        $this->pageNum = 1;
        $position = 0.00;

        $mainName = $this->getMainNameGroup();
        $mainGroup = $this->groups[$mainName];
        $this->renderHeader($mainGroup, $position);
        $this->renderDetail($mainGroup, $position);
        $this->renderFooter($mainGroup, $position);
        return $this->pdf->output();
    }

    /**
     * Get the group set as the main group.
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
     * Add a new blank page to document.
     */
    private function newPage()
    {
        $this->pdf->newPage();
        ++$this->pageNum;
    }

    /**
     * Get the vertical position for an object based
     * on the lower left corner of the page.
     *
     * @param float $posLin
     * @param float $posObj
     * @return float
     */
    private function pagePosition($posY)
    {
        return $this->pageHeight - $posY;
    }

    /**
     * Add the group detail to the PDF file.
     *
     * @param GroupItem $group
     * @param float     $position
     */
    private function renderDetail($group, &$position)
    {
        $model = $this->datasets[$group->name][self::DATASET_DETAIL] ?? null;
        if (!isset($model)) {
            return;
        }

        $subgroup = empty($group->detail->subgroup) ? null : $this->groups[$group->detail->subgroup];
        foreach ($model->data as $row) {
            // Calculate if need detail header
            $hasDetHeader = $group->detail->hasDetailRupture($row, true);
            $detHeaderHeight = $hasDetHeader ? $subgroup->header->height : 0.00;

            // Calculate remaining space into page
            $remaining = $this->pagePosition($position) - $group->footer->height;
            $required = $group->detail->height + $detHeaderHeight;
            if ($remaining < $required) {
                // Finish page and render another
                $this->renderFooter($group, $position);
                $this->newPage();

                $position = 0.00;
                $this->renderHeader($group, $position);
            }

            // Render detail header, if its needed
            if ($hasDetHeader) {
                $this->renderSubgroup($subgroup, $row, $position);
            }

            // Render detail data
            $posY = $this->pagePosition($position);
            $group->detail->render($this->pdf, $row, $posY);
            $position += $group->detail->height;
        }
    }

    /**
     * Add the group header to the PDF file.
     *
     * @param GroupItem $group
     * @param float     $position
     */
    private function renderHeader($group, &$position)
    {
        $model = $this->datasets[$group->name][self::DATASET_HEADER] ?? null;
        $data = isset($model) ? $model->data : null;
        $posY = $this->pagePosition($position);
        $group->header->render($this->pdf, $data, $posY);
        $position += $group->header->height;
    }

    /**
     * Add the group footer to the PDF file.
     *
     * @param GroupItem $group
     * @param float     $position
     */
    private function renderFooter($group, &$position)
    {
        $model = $this->datasets[$group->name][self::DATASET_FOOTER] ?? null;
        $data = isset($model) ? $model->data : null;
        $posY = $this->pagePosition($position);
        $group->footer->render($this->pdf, $data, $posY);
        $position += $group->footer->height;
    }

    /**
     *
     * @param GroupItem $group
     * @param array     $data
     * @param float     $position
     */
    private function renderSubgroup($group, $data, &$position)
    {
        // TODO: render footer previous rupture, if there are.
        $posY = $this->pagePosition($position);
        $group->header->render($this->pdf, $data, $posY);
        $position += $group->header->height;
    }
}
