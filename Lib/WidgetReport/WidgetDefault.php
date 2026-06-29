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
    public function setValue(Object $data): void
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
                $object = $data->additional[$values[0]] ?? null;
                if (false === isset($object) || false === isset($values[1])) {
                    $this->value = '';
                    break;
                }

                // method call: fieldname like "filters.method()" or "filters.method('a','b')"
                if (str_ends_with($values[1], ')')) {
                    $open = strpos($values[1], '(');
                    $method = substr($values[1], 0, $open);
                    if (false === method_exists($object, $method)) {
                        $this->value = '';
                        break;
                    }

                    $paramsStr = str_replace(["'", ' '], '', substr($values[1], $open + 1, -1));
                    $this->value = $paramsStr === ''
                        ? (string)$object->{$method}()
                        : (string)$object->{$method}(explode(',', $paramsStr));
                    break;
                }

                $this->value = $object->{$values[1]} ?? '';
        }
    }
}
