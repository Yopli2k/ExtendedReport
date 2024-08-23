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
 * Class for displaying values calculated in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetCalculated extends WidgetNumber
{

    private const OPERATOR_AVG   = 'avg';
    private const OPERATOR_COUNT = 'count';
    private const OPERATOR_MAX   = 'max';
    private const OPERATOR_MIN   = 'min';
    private const OPERATOR_SUM   = 'sum';

    /**
     * Widget type.
     *
     * @var string
     */
    protected $operator;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->operator = $data['operator'] ?? 'sum';
        $this->translate = false;

        $this->setInitValue();
    }

    /**
     * Perform widget-specific calculation.
     *
     * @param Object $data
     */
    public function process(&$data)
    {
        switch ($this->operator) {
            case self::OPERATOR_AVG:
            case self::OPERATOR_MAX:
            case self::OPERATOR_MIN:
            case self::OPERATOR_SUM:
                $newValue = (double) $this->getValueForFieldName($data) ?? 0.00;
                $this->value = $this->processNewValue($newValue);
                break;

            case self::OPERATOR_COUNT:
                $this->value += 1;
                break;
        }
    }

    /**
     * Set value from dataset to widget if fieldname is not empty.
     * If currency is not empty, set the currency code.
     *
     * @param Object $data
     */
    public function setValue(Object $data)
    {
        if (false === empty($this->currency)) {
            $this->divisa = $data->{$this->currency} ?? '';
        }
    }

    /**
     * Calculates the new value for the widget depending on the operator.
     *
     * @param mixed $newValue
     * @return mixed
     */
    private function processNewValue($newValue)
    {
        switch ($this->operator) {
            case self::OPERATOR_AVG:
                return ($this->value + $newValue) / 2;

            case self::OPERATOR_MAX:
                return ($newValue > $this->value) ? $newValue : $this->value;

            case self::OPERATOR_MIN:
                return ($this->value == 0 || $newValue < $this->value) ? $newValue : $this->value;

            case self::OPERATOR_SUM:
                return $this->value + $newValue;
        }
        return $this->value;
    }

    /**
     * Sets the initial value depending on the type of operator.
     */
    private function setInitValue()
    {
        switch ($this->operator) {
            case self::OPERATOR_AVG:
            case self::OPERATOR_MAX:
            case self::OPERATOR_MIN:
            case self::OPERATOR_SUM:
                $this->value = 0.00;
                break;

            case self::OPERATOR_COUNT:
                $this->value = 0;
                break;
        }
    }
}
