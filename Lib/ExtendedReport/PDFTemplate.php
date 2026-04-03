<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2025 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2025 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public license as
 * published by the Free Software Foundation, either version 3 of the
 * license, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public license for more details.
 *
 * You should have received a copy of the GNU Lesser General Public license
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use Cezpdf;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;

/**
 * Main class for generate PDF report from XML Report file.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class PDFTemplate extends ExportTemplate
{
    /**
     *
     * @var int
     */
    protected int $pageHeight;

    /**
     *
     * @var int
     */
    protected int $pageWidth;

    /**
     * PDF object.
     *
     * @var Cezpdf
     */
    protected Cezpdf $pdf;

    /**
     * Load XML template structure.
     *
     * @param string $name
     * @return bool
     */
    public function loadTemplate(string $name): bool
    {
        if (false === parent::loadTemplate($name)) {
            return false;
        }

        $this->pdf = new Cezpdf($this->config->page['type'], $this->config->page['orientation']);
        $this->pdf->tempPath = FS_FOLDER . '/MyFiles/Cache';
        $this->pdf->selectFont($this->config->font['type']);
        $this->pdf->addInfo('Creator', 'ExtendedReport for FacturaScripts');
        $this->pdf->addInfo('Producer', 'ExtendedReport');
        $this->pageWidth = (int)$this->pdf->ez['pageWidth'];
        $this->pageHeight = (int)$this->pdf->ez['pageHeight'];
        return true;
    }

    /**
     * Create the PDF file according to the template loaded.
     *
     * @return string
     */
    public function render(): string
    {
        if (false === isset($this->pdf) || empty($this->groups)) {
            return '';
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
     * Return render special configuration.
     *
     * @return array
     */
    protected function getDefaultRenderCfg(): array
    {
        return array_merge(parent::getDefaultRenderCfg(),
            [
                'pageBreakOnRupture' => false,
            ]
        );
    }

    /**
     * Add the group detail to the PDF file.
     *
     * @param GroupItem $group
     * @param float     $position
     */
    protected function renderDetail(GroupItem $group, float &$position): void
    {
        $model = $this->datasets[$group->name] ?? $this->datasets['main'] ?? null;
        if (false === isset($model)) {
            return;
        }

        $detailGroup = $group->getDetailGroup();
        $detail = $group->getDetail();
        $footerHeight = $group->getFooterHeight(true);
        $previousRow = null;
        $firstRow = reset($model->data);
        if ($detailGroup && $firstRow !== false && $this->hasGroupValue($detail->fieldName, $firstRow)) {
            $this->renderHeader($detailGroup, $position, $firstRow);
        }

        foreach ($model->data as $row) {
            // Calculate if there are rupture
            //  - render subgroup footer from previous row
            //  - need detail header
            $hasRupture = $detail->hasFieldRupture($row, true);
            if ($hasRupture && $detailGroup && $previousRow !== null) {
                $this->renderFooter($detailGroup, $position, $previousRow);
                $this->resetCalculateColumns($detailGroup, true);

                // Force new page if page break configuration is active.
                if ($this->defaultData->getRenderCfg('pageBreakOnRupture', false)) {
                    $this->newPageWithBands($group, $position, $row);
                }
            }
            $previousRow = $row;
            $detHeaderHeight = ($hasRupture && $detailGroup)
                ? $detailGroup->getHeaderHeight(false)
                : 0.00;

            // Calculate remaining space into page
            $remaining = $this->pagePosition($position) - $footerHeight;
            $required = $detail->height + $detHeaderHeight;
            if ($remaining < $required) {
                // Finish page and render another
                $this->newPageWithBands($group, $position, $row);
            }

            // Render detail header, if its needed
            if ($hasRupture && $detailGroup && $detail->hasFieldValue($row)) {
                $this->renderHeader($detailGroup, $position, $row); // render only detail headers
            }

            // Render detail data
            $posY = $this->pagePosition($position);
            $detail->render($this->pdf, $this->defaultData, $row, $posY);
            $this->processCalculateColumns($group, $row, true);
            if ($detailGroup) {
                $this->processCalculateColumns($detailGroup, $row, true);
            }
            $position += $detail->height;
        }

        if ($detailGroup && $previousRow !== null) {
            $this->renderFooter($detailGroup, $position, $previousRow);
            $this->resetCalculateColumns($detailGroup, true);
        }
    }

    /**
     * Add the group header to the PDF file.
     *
     * @param GroupItem   $group
     * @param float       $position
     * @param ?Object     $data
     * @param bool        $second
     */
    protected function renderHeader(GroupItem $group, float &$position, ?Object $data = null, bool $second = false): void
    {
        $header = $group->getHeader($second);
        if (isset($header)) {
            if ($data == null) {
                $model = $this->datasets[$group->name] ?? $this->datasets['main'] ?? null;
                $data = isset($model) ? reset($model->data) : null;
            }
            $posY = $this->pagePosition($position);
            $header->render($this->pdf, $this->defaultData, $data, $posY);
            $position += $header->height;
        }
    }

    /**
     * Add the group footer to the PDF file.
     *
     * @param GroupItem   $group
     * @param float       $position
     * @param ?Object     $data
     * @param bool        $second
     */
    protected function renderFooter(GroupItem $group, float &$position, ?Object $data = null, bool $second = false): void
    {
        $footer = $group->getFooter($second);
        if (isset($footer)) {
            $model = $this->datasets[$group->name] ?? $this->datasets['main'] ?? null;
            if ($data == null) {
                $data = isset($model) ? reset($model->data) : null;
            }
            $posY = $this->pagePosition($position);
            $footer->render($this->pdf, $this->defaultData, $data, $posY);
            $position += $footer->height;
        }
    }

    /**
     * Indicate if field group rupture has value.
     *
     * @param string $field
     * @param $row
     * @return bool
     */
    private function hasGroupValue(string $field, $row): bool
    {
        $value = $row->{$field} ?? null;
        return $value !== null && $value !== '';
    }

    /**
     * Add a new blank page to document.
     */
    private function newPage(): void
    {
        $this->pdf->newPage();
        $this->defaultData->addPage();
    }

    /**
     * Make a new page render footer and new page with header.
     *
     * @param GroupItem $group
     * @param float $position
     * @param ?Object $data
     * @return void
     */
    private function newPageWithBands(GroupItem $group, float &$position, ?Object $data = null): void
    {
        // Finish page and render another
        $this->renderFooter($group, $position, $data, true);
        $this->newPage();

        $position = 0.00;
        $this->renderHeader($group, $position, $data, true); // render all headers
    }

    /**
     * Get the vertical position for an object based
     * the lower left corner of the page.
     *
     * @param float $posY
     * @return float
     */
    private function pagePosition(float $posY): float
    {
        return $this->pageHeight - $posY;
    }

    /**
     * Execute the calculation of special columns in the bands.
     *
     * @param GroupItem $group
     * @param Object $data
     * @param bool $second
     */
    private function processCalculateColumns(GroupItem $group, Object $data, bool $second = false): void
    {
        $footer = $group->getFooter($second);
        $footer?->calculate($data);
    }

    /**
     * Reset
     *
     * @param GroupItem $group
     * @param bool $second
     * @return void
     */
    private function resetCalculateColumns(GroupItem $group, bool $second = false): void
    {
        $footer = $group->getFooter($second);
        if ($footer === null) {
            return;
        }

        foreach ($footer->columns as $column) {
            if (method_exists($column->widget, 'reset')) {
                $column->widget->reset();
            }
        }
    }
}
