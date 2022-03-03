<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Plugins\ExtendedReport\Model\Report\TestReport;

/**
 * Report test
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ReportTest extends Controller
{

    /**
     *
     * @var TestReport
     */
    private $model;

    /**
     * Initialize all objects and properties.
     *
     * @param string $className
     * @param string $uri
     */
    public function __construct(string $className, string $uri = '') {
        parent::__construct($className, $uri);
        $this->model = new TestReport();
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pagedata = parent::getPageData();
        $pagedata['title'] = 'report-test';
        $pagedata['icon'] = 'fas fa-print';
        $pagedata['menu'] = 'admin';
        $pagedata['showonmenu'] = true;         // change to false for production version.

        return $pagedata;
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response $response
     * @param User $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);
        $this->loadReportData();
        $this->execAfterAction($this->request->get('action', ''));
    }

    /**
     * Execute the informed action.
     *
     * @param string $action
     */
    protected function execAfterAction(string $action)
    {
        if ($action == 'print') {

        }
    }

    /**
     * Load test data into model.
     */
    private function loadReportData()
    {
        $this->model->loadData();
    }
}
