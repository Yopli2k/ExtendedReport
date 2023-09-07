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

/**
 * Class for displaying texts in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetNumber extends WidgetLabel
{

    /**
     * the number of decimal places to display.
     *
     * @var int
     */
    protected $decimal;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->align = isset($data['align']) ? $data['align'] : 'right';
        $this->translate = false;
        $this->decimal = isset($data['decimal']) ? (int) $data['decimal'] : FS_NF0;
    }

    /**
     * Obtain the value to be represented.
     *
     * @return mixed
     */
    protected function getValue()
    {
        $thousand = FS_NF2;
        if (empty(FS_NF2)) {
            $thousand = FS_NF1 === '.' ? ',' : '.';
        }
        return number_format((float)$this->value, (int)$this->decimal, FS_NF1, $thousand);
    }
}
