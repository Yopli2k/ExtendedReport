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

use FacturaScripts\Core\Model\User;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ConfigItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\GroupItem;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ReportDefaultData;
use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\ReportItemLoadEngine;

/**
 * Base class for export report from XML Report file.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class ExportTemplate
{
    /**
     * Name of the template report.
     *
     * @var string
     */
    public string $name;

    /**
     * Template configuration
     *
     * @var ConfigItem|array
     */
    public ConfigItem|array $config;

    /**
     * Template structure
     *
     * @var GroupItem[]
     */
    public array $groups;

    /**
     * List of models with the data.
     *
     * @var ModelReport[]
     */
    protected array $datasets = [];

    /**
     * Default data for the report.
     *
     * @var ReportDefaultData
     */
    protected ReportDefaultData $defaultData;

    /**
     * Render the report and return the output.
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Class constructor. Get initial values from params.
     *
     * @param User $user
     * @param Empresa $company
     * @param array $additional
     */
    public function __construct(User $user, Empresa $company, array $additional = [])
    {
        $this->defaultData = new ReportDefaultData($user, $company);
        foreach ($additional as $key => $value) {
            $this->defaultData->additional[$key] = $value;
        }
    }

    /**
     * Add source data for the band named.
     *
     * @param string $name
     * @param ModelReport $model
     */
    public function addDataset(string $name, ModelReport $model): void
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
        return true;
    }
}
