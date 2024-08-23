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

use FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport\WidgetLabel;

/**
 * Class for displaying defaults texts in the report.
 *    - Company data
 *    - User data
 *    - Date/Time of System
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetDefault extends WidgetLabel
{

    private const AUTO_TEXT_COMPANY = 'company';
    private const AUTO_TEXT_DATE = 'date';
    private const AUTO_TEXT_PAGE = 'page';
    private const AUTO_TEXT_TIME = 'time';
    private const AUTO_TEXT_USER = 'user';

    /**
     * Class constructor. Load initials values from data array.
     *   - Force no translate value.
     *
     * @param array $data
     */
    public function __construct($data) {
        parent::__construct($data);
        $this->translate = false;
    }

    /**
     * Set value from dataset to widget
     *
     * @param Object $data
     */
    public function setValue(Object $data)
    {
        $values = explode('.', $this->fieldname);
        switch ($values[0]) {
            case self::AUTO_TEXT_COMPANY:
                $this->value = $data->getCompanyFieldValue($values[1]);
                break;

            case self::AUTO_TEXT_DATE:
                $this->value = date('d-m-Y');
                break;

            case self::AUTO_TEXT_TIME:
                $this->value = date('H:i:s');
                break;

            case self::AUTO_TEXT_PAGE:
                $this->value = $data->getPageNum();
                break;

            case self::AUTO_TEXT_USER:
                $this->value = $data->getUserFieldValue($values[1]);
                break;

            default:
                $this->value = isset($data->additional[$values[0]])
                    ? $data->additional[$values[0]]->{$values[1]} ?? ''
                    : '';
        }
    }
}
