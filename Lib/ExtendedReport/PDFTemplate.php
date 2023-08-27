<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2015-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport  Copyright (C) 2023-2023 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use Cezpdf;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Dinamic\Model\User;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ConfigItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ReportDefaultData;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ReportItemLoadEngine;
use FacturaScripts\Plugins\ExtendedReport\Model\Base\ModelReport;

/**
 * Main class for generate PDF report from XML Report file.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class PDFTemplate
{

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
     * @var PDFDefaultData
     */
    protected $defaultData;

    /**
     *
     * @var int
     */
    protected $pageHeight;

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
     *
     * @param User $user
     * @param Empresa $company
     */
    public function __construct($user, $company) {
        $this->defaultData = new ReportDefaultData($user, $company);
    }

    /**
     * Add source data for the band named.
     *
     * @param string $name
     * @param ModelReport $model
     */
    public function addDataset(string $name, $model)
    {
        $this->datasets[$name] = $model;
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
        $this->pdf->addInfo('Creator', 'ExtendedReport for FacturaScripts');
        $this->pdf->addInfo('Producer', 'ExtendedReport');
        $this->pdf->tempPath = FS_FOLDER . '/MyFiles/Cache';
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
        if (!isset($this->pdf) || empty($this->groups)) {
            return;
        }

        $this->defaultData->setPageNum(1);
        $position = 0.00;

        foreach ($this->groups as $group) {
            $this->renderHeader($group, $position);
            $this->renderDetail($group, $position);
            $this->renderFooter($group, $position);
        }
        return $this->pdf->output();
    }

    /**
     * Add the group detail to the PDF file.
     *
     * @param GroupItem $group
     * @param float     $position
     */
    protected function renderDetail($group, &$position)
    {
        $detail = $group->getDetail();
        if (!isset($detail)) {
            return;
        }

        $model = $this->datasets[$group->name] ?? null;
        if (!isset($model)) {
            return;
        }

        $footerHeight = $group->getFooterHeight(true);
        foreach ($model->data as $row) {
            // Calculate if need detail header
            $hasRupture = $detail->hasFieldRupture($row, true);
            $detHeaderHeight = $hasRupture ? $group->detail->getHeaderHeight(false) : 0.00;

            // Calculate remaining space into page
            $remaining = $this->pagePosition($position) - $footerHeight;
            $required = $detail->height + $detHeaderHeight;
            if ($remaining < $required) {
                // Finish page and render another
                $this->renderFooter($group, $position, $row, true);
                $this->newPage();

                $position = 0.00;
                $hasRupture = false;
                $this->renderHeader($group, $position, $row, true); // render all headers
            }

            // Render detail header, if its needed
            if ($hasRupture) {
                $this->renderHeader($group->detail, $position, $row, false); // render only detail headers
            }

            // Render detail data
            $posY = $this->pagePosition($position);
            $detail->render($this->pdf, $this->defaultData, $row, $posY);
            $this->procesCalculateColumns($group, $row, true);
            $position += $detail->height;
        }
    }

    /**
     * Add the group header to the PDF file.
     *
     * @param GroupItem   $group
     * @param float       $position
     * @param Object|null $data
     * @param bool        $second
     */
    protected function renderHeader($group, &$position, $data = null, $second = false)
    {
        $header = $group->getHeader($second);
        if (isset($header)) {
            if ($data == null) {
                $model = $this->datasets[$group->name] ?? null;
                $data = isset($model) ? $model->data[0] : null;
            }
            $posY = $this->pagePosition($position);
            $header->render($this->pdf, $this->defaultData, $data, $posY);
            $position += $header->height;

            if ($group->detail instanceof GroupItem) {
                $this->renderHeader($group->detail, $position, false);
            }
        }
    }

    /**
     * Add the group footer to the PDF file.
     *
     * @param GroupItem   $group
     * @param float       $position
     * @param Object|null $data
     * @param bool        $second
     */
    protected function renderFooter($group, &$position, $data = null, $second = false)
    {
        $footer = $group->getFooter($second);
        if (isset($footer)) {
            $model = $this->datasets[$group->name] ?? null;
            if ($data == null) {
                $data = isset($model) ? $model->data[0] : null;
            }
            $posY = $this->pagePosition($position);
            $footer->render($this->pdf, $this->defaultData, $data, $posY);
            $position += $footer->height;
        }
    }

    /**
     * Add a new blank page to document.
     */
    private function newPage()
    {
        $this->pdf->newPage();
        $this->defaultData->addPage();
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
     * Execute the calculation of special columns in the bands.
     *
     * @param GroupItem $group
     * @param Object $data
     */
    private function procesCalculateColumns($group, $data, $second = false)
    {
        $footer = $group->getFooter($second);
        if (isset($footer)) {
            $footer->calculate($data);
        }
    }
}
