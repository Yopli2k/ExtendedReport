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

use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;

/**
 * Main class for generate CSV report from XML Report file.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class CSVTemplate extends ExportTemplate
{
    private bool $addHeader = false;

    /**
     * Contains the CSV data in array format
     *
     * @var array
     */
    private array $csv = [];

    /**
     * Text delimiter value
     *
     * @var string
     */
    private string $delimiter = '"';

    /**
     * Separator value
     *
     * @var string
     */
    private string $separator = ';';

    /**
     * Generates the CSV content from the template data.
     *
     * @return string
     */
    public function render(): string
    {
        if (empty($this->groups)) {
            return '';
        }

        foreach ($this->groups as $group) {
            $this->addHeader = true;
            $this->renderDetail($group);
        }
        return implode(PHP_EOL, $this->csv);
    }

    /**
     * Add the group detail to the CSV file.
     *
     * @param GroupItem $group
     */
    protected function renderDetail(GroupItem $group): void
    {
        $model = $this->datasets[$group->name] ?? null;
        if (false === isset($model)) {
            return;
        }

        $detail = $group->getDetail();
        foreach ($model->data as $row) {
            $data = $detail->values($this->defaultData, $row);
            if ($this->addHeader) {
                $this->csv[] = implode($this->separator, array_map(fn($field) => $this->delimiter . $field . $this->delimiter, array_keys($data)));
                $this->addHeader = false;
            }
            $this->csv[] = implode($this->separator, array_map(fn($field) => $this->delimiter . $field . $this->delimiter, array_values($data)));
        }
    }
}
