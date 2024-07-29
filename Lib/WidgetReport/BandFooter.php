<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2024 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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

/**
 * Specific band for the report footer data.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandFooter extends BandItem
{

    /**
     *
     * @var bool
     */
    public $forceNewPage;

    /**
     *
     * @var bool
     */
    public $placeBottom;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->forceNewPage = $data['newpage'] ?? false;
        $this->placeBottom = $data['placebottom'] ?? false;
    }

    /**
     *
     *
     * @param Object $data
     */
    public function calculate(&$data)
    {
        foreach ($this->columns as $column) {
            if (method_exists($column->widget, 'process')) {
                $column->widget->process($data);
            }
        }
    }
}
