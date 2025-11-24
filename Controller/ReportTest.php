<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2025 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2025 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
namespace FacturaScripts\Plugins\ExtendedReport\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\KernelException;
use FacturaScripts\Core\Response;
use FacturaScripts\Dinamic\Model\Empresa;
use FacturaScripts\Dinamic\Model\User;
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
     * Selected company.
     *
     * @var Empresa
     */
    public $empresa;

    /**
     *
     * @var TestReport
     */
    private TestReport $model;

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
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'report-test';
        $pageData['icon'] = 'fa-solid fa-print';
        $pageData['menu'] = 'admin';
        $pageData['showonmenu'] = true;         // change to false for production version.
        return $pageData;
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response $response
     * @param User $user
     * @param ControllerPermissions $permissions
     * @throws KernelException
     */
    public function privateCore(&$response, $user, $permissions): void
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
    protected function execAfterAction(string $action): void
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
     * @param string $action
     */
    private function loadReportData(string $action): void
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
    private function printColumnTemplate(string $action): void
    {
        $test = ucfirst(explode('-', $action)[0]);
        $template = new PDFTemplate($this->user, $this->empresa);
        if (false === $template->loadTemplate('ColumnTest' . $test)) {
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
    private function printReport(): void
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
