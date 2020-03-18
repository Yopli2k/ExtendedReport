<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedController;

use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFTemplate;

/**
 * Controller for report data and sumary data
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class ReportController extends ListController
{

    const MODEL_REPORT_NAMESPACE = self::MODEL_NAMESPACE . 'ModelReport\\';

    /**
     * Initializes all the objects and properties.
     *
     * @param string $className
     * @param string $uri
     */
    public function __construct(string $className, string $uri = '')
    {
        parent::__construct($className, $uri);
        $this->setTemplate('Master/ReportController');
    }

    /**
     * Disable the auto submit form in the filters fields
     *
     * @param BaseView $view
     */
    protected function addFilter($viewName, $key, $filter)
    {
        parent::addFilter($viewName, $key, $filter);
        $filter->autoSubmit = false;
    }

    /**
     * Creates and adds a ReportView to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $icon
     */
    protected function addView($viewName, $modelName, $viewTitle = '', $icon = 'fa-search')
    {
        $title = empty($viewTitle) ? $this->title : $viewTitle;
        $view = new ReportView($viewName, $title, self::MODEL_REPORT_NAMESPACE . $modelName, $icon);
        $this->addCustomView($viewName, $view);
        $this->setSettings($viewName, 'megasearch', false);
    }

    /**
     * Load data for active view. For greater performance, only for active view
     *
     * @param string $viewName
     * @param BaseView $view
     */
    protected function loadData($viewName, $view)
    {
        if ($this->active == $viewName) {
            $action = $this->request->get('action', '');
            if ($action == 'load' || $action == 'print') {
                $view->loadData();
            }
        }
    }

    /**
     * Runs the controller actions after data read.
     *
     * @param string $action
     */
    protected function execAfterAction($action)
    {
        switch ($action) {
            case 'print':
                $this->printReport();
                break;

            default:
                parent::execAfterAction($action);
                break;
        }
    }

    protected function printReport()
    {
        $template = new PDFTemplate();
        if (!$template->loadTemplate('ReportAttendance')) {
            return;
        }

        $view = $this->views[$this->active];
        $template->addDataset('main', $view->model);
        $template->addDataset('detail', $view->model);
        $pdf = $template->render();

        $this->setTemplate(false);
        $this->response->headers->set('Content-type', 'application/pdf');
        $this->response->headers->set('Content-Disposition', 'inline;filename=prueba.pdf');
        $this->response->setContent($pdf);
    }
}
