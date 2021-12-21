<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Controller;

use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedController\ReportController;

/**
 * Report test
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ReportTest extends ReportController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'report-test';
        $pagedata['icon'] = 'fas fa-file-alt';
        $pagedata['menu'] = 'admin';
        $pagedata['showonmenu'] = true;         // change to false for production version.

        return $pagedata;
    }

    /**
     * Add Report Views
     */
    protected function createViews($viewName = 'ReportTest')
    {
        $this->addView($viewName, 'TestReport', 'test', 'fas fa-checks');
    }
}
