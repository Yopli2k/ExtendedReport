<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Model\Report\Data;

/**
 * Class to manage test data for extended report
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class TestData
{

    /**
     * Date test data
     *
     * @var string
     */
    public $date;

    /**
     * Record identifier
     *
     * @var int
     */
    public $id;

    /**
     * Record human identification
     *
     * @var type
     */
    public $name;

    /**
     * Float test data
     *
     * @var float
     */
    public $total;
}
