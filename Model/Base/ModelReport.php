<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
     * Execute the load data for report
     *
     * @param BaseFilter[] $filters
     * @param DataBaseWhere[] $where
     * @param array $order
     * @param int $offset
     * @param int $limit
     * @return ModelReport[]
     */
    abstract public function all($filters, $where, $order, $offset, $limit);

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
     * Calculate DataBaseWhere from key list of filters
     *
     * @param BaseFilter[] $filters
     * @param string[] $keys
     * @return DataBaseWhere[]
     */
    protected function getFiltersWhere($filters, $keys = [])
    {
        $result = [];
        $keys_values = empty($keys) ? array_keys($filters) : $keys;
        foreach ($keys_values as $filterKey) {
            $filters[$filterKey]->getDataBaseWhere($result);
        }
        return $result;
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
