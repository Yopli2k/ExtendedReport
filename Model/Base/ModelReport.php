<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Model\Base;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\ToolBox;

/**
 * Description of ModelReport
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class ModelReport
{

    /**
     * It provides direct access to the database.
     *
     * @var DataBase
     */
    protected static $dataBase;

    /**
     *
     * @var array
     */
    public $data = [];

    /**
     *
     * @var array
     */
    protected $totals = [];

    /**
     * Load report data into array data property.
     */
    abstract public function loadData();

    /**
     * Class constructor.
     */
    public function __construct()
    {
        if (self::$dataBase === null) {
            self::$dataBase = new DataBase();
        }
    }

    /**
     * Accumulate the amount in the indicated total field.
     *
     * @param string $fieldName
     * @param float $amount
     */
    public function accumulate($fieldName, $amount)
    {
        $value = $this->getTotal($fieldName);
        $this->totals[$fieldName] = $value + $amount;
    }

    /**
     * Returns the accumulated amount of the requested field.
     *
     * @param string $fieldName
     * @return float
     */
    public function getTotal($fieldName)
    {
        return isset($this->totals[$fieldName]) ? $this->totals[$fieldName] : 0.00;
    }

    /**
     *
     * @return ToolBox
     */
    protected static function toolBox()
    {
        return new ToolBox();
    }
}
