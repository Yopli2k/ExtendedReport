<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedController;

use FacturaScripts\Core\Lib\AssetManager;
use FacturaScripts\Core\Lib\ExtendedController\ListView;
use FacturaScripts\Plugins\ExtendedReport\Model\Base\ModelReport;

/**
 * View definition for its use in ReportController
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ReportView extends ListView
{

    /**
     * Model to use in this view.
     *
     * @var ModelReport
     */
    public $model;

    /**
     * Indicate the template to see the detail of the report data.
     *
     * @var string
     */
    public $templateData;

    /**
     * ReportView constructor and initialization.
     *
     * @param string $name
     * @param string $title
     * @param string $modelName
     * @param string $icon
     */
    public function __construct($name, $title, $modelName, $icon)
    {
        parent::__construct($name, $title, $modelName, $icon);
        $this->template = 'Master/ReportView.html.twig';
        $this->templateData = '';
        $this->showFilters = true;
    }

    /**
     * Adds assets to the asset manager.
     */
    protected function assets()
    {
        parent::assets();
        AssetManager::add('js', FS_ROUTE . '/Dinamic/Assets/JS/ReportView.js');
    }

    /**
     * Loads the data in the cursor.
     *
     * @param mixed           $code
     * @param DataBaseWhere[] $where
     * @param array           $order
     * @param int             $offset
     * @param int             $limit
     */
    public function loadData($code = false, $where = [], $order = [], $offset = -1, $limit = 0)
    {
        $this->offset = ($offset < 0) ? $this->offset : $offset;
        $this->order = empty($order) ? $this->order : $order;
        $this->where = array_merge($where, $this->where);

        $this->model->loadData($this->filters, $this->where, $this->order, $offset, $limit);
        $this->cursor = &$this->model->data; // for compatibility
        $this->count = count($this->cursor);
    }
}
