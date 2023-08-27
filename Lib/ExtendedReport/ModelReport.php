<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2015-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport  Copyright (C) 2023-2023 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\ToolBox;

/**
 * Model base for XML reports.
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
     *
     * @return ToolBox
     */
    protected static function toolBox()
    {
        return new ToolBox();
    }
}
