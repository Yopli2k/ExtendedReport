<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFTemplate;
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
        $action = $this->request->get('action', '');
        $this->loadReportData($action);
        $this->execAfterAction($action);
    }

    /**
     * Execute the informed action.
     *
     * @param string $action
     */
    protected function execAfterAction(string $action)
    {
        switch ($action) {
            case 'print-test':
                $this->printReport();
            break;

            case 'landscape-test':
            case 'portrait-test':
                $this->printColumnTemplate($action);
            break;
        }
    }

    /**
     * Load test data into model.
     *
     * @param $action
     */
    private function loadReportData(string $action)
    {
        switch ($action) {
            case 'print-test':
                $this->model->loadData();
            break;

            case 'landscape-test':
            case 'portrait-test':
                $max = $action == 'portrait-test' ? 800 : 560;
                $this->model->loadDataColumns($max);
            break;
        }
    }

    /**
     * Print template with columns position reference.
     */
    private function printColumnTemplate(string $action)
    {
        $test = ucfirst(\explode('-', $action)[0]);
        $template = new PDFTemplate($this->user, $this->empresa);
        if (!$template->loadTemplate('ColumnTest' . $test)) {
            return;
        }

        $template->addDataset('main', $this->model);
        $pdf = $template->render();

        $this->setTemplate(false);
        $this->response->headers->set('Content-type', 'application/pdf');
        $this->response->headers->set('Content-Disposition', 'inline;filename=ColumnTest.pdf');
        $this->response->setContent($pdf);
    }

    /**
     * Print report with ramdom data.
     */
    private function printReport()
    {
        $template = new PDFTemplate($this->user, $this->empresa);
        if (!$template->loadTemplate('ReportTest')) {
            return;
        }

        $template->addDataset('main', $this->model);
        $pdf = $template->render();

        $this->setTemplate(false);
        $this->response->headers->set('Content-type', 'application/pdf');
        $this->response->headers->set('Content-Disposition', 'inline;filename=ReportTest.pdf');
        $this->response->setContent($pdf);
    }
}
