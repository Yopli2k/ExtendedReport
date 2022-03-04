<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
 * Specific band for the report detail data.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandDetail extends BandItem
{

    /**
     * Name of the field that determines the break in the data sequence.
     *
     * @var string
     */
    public $fieldName;

    /**
     * Last value for fieldName.
     *
     * @var string
     */
    public $fieldValue = null;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param type $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->fieldName = $data['fieldname'] ?? '';
    }

    /**
     * Determine if there is a break in the sequence detail data.
     *
     * @param object $data
     * @param bool   $update
     * @return bool
     */
    public function hasFieldRupture($data, $update = false): bool
    {
        if (empty($this->fieldName)) {
            return false;
        }

        try {
            $value = $data->{$this->fieldName};
            if ($this->fieldValue == null) {
                $this->fieldValue = $value;
                return false;
            }

            $result = ($this->fieldValue != $value);
            if ($update && $result) {
                $this->fieldValue = $value;
            }
        } catch (\Exception $exc) {
            $result = false;
        }

        return $result;
    }
}
