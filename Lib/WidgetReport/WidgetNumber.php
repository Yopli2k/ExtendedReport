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

use FacturaScripts\Core\DataSrc\Divisas;
use FacturaScripts\Core\Tools;

/**
 * Class for displaying texts in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetNumber extends WidgetLabel
{

    /**
     * If value is an amount then indicates the field name for get the currency symbol.
     *
     * @var string
     */
    protected $currency;

    /**
     * the number of decimal places to display.
     *
     * @var int
     */
    protected $decimal;

    /**
     * The currency code to use.
     *
     * @var string
     */
    protected string $divisa = '';

    /**
     * Indicates the symbol or icon to add to the left of the value.
     *
     * @var string
     */
    protected string $licon;

    /**
     * The colour has to use when representing the negative value data.
     *
     * @var array
     */
    protected array $negativecolor;

    /**
     * @var bool
     */
    protected $printempty;

    /**
     * Indicates the symbol or icon to add to the right of the value.
     *
     * @var string
     */
    protected string $ricon;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->translate = false;
        $this->align = $data['align'] ?? 'right';
        $this->decimal = isset($data['decimal']) ? (int) $data['decimal'] : FS_NF0;
        $this->printempty = isset($data['printempty'])
            ? filter_var($data['printempty'], FILTER_VALIDATE_BOOLEAN)
            : true;

        $this->currency = $data['currency'] ?? '';
        $this->licon = $data['licon'] ?? '';
        $this->ricon = $data['ricon'] ?? '';

        $color = $data['negative'] ?? false;
        $this->negativecolor = $color
            ? $this->rgbFromColor($color)
            : $this->color;
    }

    /**
     * Set value from dataset to widget if fieldname is not empty.
     * If the currency field is not empty, then get the currency configuration.
     *
     * @param Object $data
     */
    public function setValue(Object $data)
    {
        parent::setValue($data);
        if (false === empty($this->currency)) {
            $this->divisa = $data->{$this->currency} ?? '';
        }
    }

    /**
     * Get the color to be represented.
     * If the value is negative, the negative color will be used.
     *
     * @return array
     */
    protected function getColor(): array
    {
        $value = (float) $this->value ?? 0.00;
        return ($value < 0.00)
            ? $this->negativecolor
            : parent::getColor();
    }

    /**
     * Get the value to be represented.
     * If the value is empty and printempty is false, then return an empty string.
     * If the value is an amount, then format the value with the currency symbol.
     *
     * @return string
     */
    protected function getValue(): string
    {
        $thousand = FS_NF2;
        if (empty(FS_NF2)) {
            $thousand = (FS_NF1 === '.') ? ',' : '.';
        }

        if (false === $this->printempty && empty($this->value)) {
            return '';
        }

        $value = number_format((float)$this->value, $this->decimal, FS_NF1, $thousand);
        if (empty($this->currency) || empty($this->divisa)) {
            return $this->licon . $value . $this->ricon;    // it is not a currency
        }

        // it is a currency
        $currencyPosition = Tools::settings('default', 'currency_position', 'right');
        return $currencyPosition === 'right'
            ? $value . ' ' . Divisas::get($this->divisa)->simbolo
            : Divisas::get($this->divisa)->simbolo . ' ' . $value;
    }
}
