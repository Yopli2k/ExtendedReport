<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2024 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use FacturaScripts\Core\Model\User;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Empresa;
use Symfony\Component\HttpFoundation\Response;

/**
 * Main class for an executed PDF report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class PDFReport
{
    /** @var PDFTemplate */
    private $pdfTemplate;

    /** @var Response */
    protected $response;

    /**
     * Class constructor
     */
    public function __construct(Response $response, array $aditional = [])
    {
        $user = $this->getUser();
        $company = $this->getCompany();
        $this->pdfTemplate = new PDFTemplate($user, $company, $aditional);
        $this->response = $response;
    }

    /**
     * Load the report template and report data.
     *
     * @param $model
     * @param $template
     * @return bool
     */
    public function load($model, string $template, string $group = 'main'): bool
    {
        if (false === $this->pdfTemplate->loadTemplate($template)) {
            return false;
        }

        $model->loadData();
        $this->pdfTemplate->addDataset($group, $model);
        return true;
    }

    /**
     * Show the PDF report.
     * Set the file name for if user donwload the file.
     *
     * @param string $fileName
     * @return void
     */
    public function show(string $fileName)
    {
        $pdf = $this->pdfTemplate->render();
        $this->response->headers->set('Content-type', 'application/pdf');
        $this->response->headers->set('Content-Disposition', 'inline;filename=' . $fileName . '.pdf');
        $this->response->setContent($pdf);
    }

    /**
     * Get the default company.
     *
     * @return Empresa
     */
    protected function getCompany(): Empresa
    {
        $idcompany = Tools::settings('default', 'idempresa');
        $company = new Empresa();
        $company->loadFromCode($idcompany);
        return $company;
    }

    /**
     * Get the current user.
     *
     * @return User
     */
    protected function getUser(): User
    {
        return Session::user();
    }
}
