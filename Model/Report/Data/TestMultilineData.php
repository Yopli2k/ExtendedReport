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
namespace FacturaScripts\Plugins\ExtendedReport\Model\Report\Data;

/**
 * Class to manage multiline test data for extended report.
 * Each record holds, per period, the current/previous/difference values that the
 * multiline report stacks into a single row. Index 0 of each array is the total.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class TestMultilineData
{
    /**
     * Record code.
     *
     * @var string
     */
    public $code;

    /**
     * Current period values. Index 0 = total, 1..n = periods.
     *
     * @var array
     */
    public array $current = [];

    /**
     * Difference (current - previous). Index 0 = total, 1..n = periods.
     *
     * @var array
     */
    public array $difference = [];

    /**
     * Record human identification.
     *
     * @var string
     */
    public $name;

    /**
     * Previous period values. Index 0 = total, 1..n = periods.
     *
     * @var array
     */
    public array $previous = [];
}
