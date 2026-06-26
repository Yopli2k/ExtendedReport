<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2026 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2026 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use Cezpdf;

/**
 * Class to manage the data columns of the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ColumnItem
{
    /**
     * Report area the column belongs to ('meta' for header metadata). Empty by
     * default, meaning the column is part of the data table. Only read by the
     * HTML render engine; the PDF ignores it.
     *
     * @var string
     */
    public string $area;

    public int $height;

    /**
     * Hide this column in the PDF output.
     *
     * @var bool
     */
    public bool $hideOnPdf;

    /**
     * Hide this column in the on-screen HTML output.
     *
     * @var bool
     */
    public bool $hideOnView;

    /**
     * Position on x-axis.
     *
     * @var int
     */
    public int $posx;

    /**
     * Position on the y-axis.
     *
     * @var int
     */
    public int $posy;

    /**
     * Display object configuration
     *
     * @var WidgetItem
     */
    public WidgetItem $widget;

    /**
     * Column width.
     *
     * @var int
     */
    public int $width;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->area = $data['area'] ?? '';
        $this->hideOnPdf = isset($data['hideonpdf']) && $data['hideonpdf'];
        $this->hideOnView = isset($data['hideonview']) && $data['hideonview'];
        $this->posx = isset($data['posx']) ? (int) $data['posx'] : 0;
        $this->posy = isset($data['posy']) ? (int) $data['posy'] : 0;
        $this->width = isset($data['width']) ? (int) $data['width'] : 30;
        $this->height = isset($data['height']) ? (int) $data['height'] : 15;
        $this->loadWidget($data['children']);
    }

    /**
     * Get the value of the column.
     *
     * @param ReportDefaultData $default
     * @param Object $data
     * @return string
     */
    public function getValue(ReportDefaultData $default, Object $data): string
    {
        $values = ($this->widget instanceof WidgetDefault) ? $default : $data;
        $this->widget->setValue($values);
        return $this->widget->getValue();
    }

    /**
     * Add column to the PDF file.
     *
     * @param Cezpdf $pdf
     * @param ReportDefaultData $default
     * @param Object $data
     * @param float  $linePos
     */
    public function render(Cezpdf $pdf, ReportDefaultData $default, Object $data, float $linePos): void
    {
        if ($this->hideOnPdf) {
            return;
        }

        $posY = $linePos - $this->posy;
        $values = ($this->widget instanceof WidgetDefault) ? $default : $data;
        $this->widget->setValue($values);
        $this->widget->render($pdf, $this->posx, $posY, $this->width, $this->height);
    }

    /**
     * Get the column data in a neutral structure for HTML output.
     * Unlike getValue(), it preserves the geometry hints (posx, posy, width) and
     * the fieldname, so the render engine can rebuild the layout semantically.
     *
     * @param ReportDefaultData $default
     * @param Object $data
     * @return array
     */
    public function toHtmlData(ReportDefaultData $default, Object $data): array
    {
        $values = ($this->widget instanceof WidgetDefault) ? $default : $data;
        $this->widget->setValue($values);

        return array_merge($this->widget->toHtmlData(), [
            'posx' => $this->posx,
            'posy' => $this->posy,
            'width' => $this->width,
            'fieldname' => $this->widget->getFieldName(),
            'area' => $this->area,
            'hideonview' => $this->hideOnView,
        ]);
    }

    /**
     * Create the visual structure for each column.
     *
     * @param array $children
     */
    protected function loadWidget(array $children): void
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
