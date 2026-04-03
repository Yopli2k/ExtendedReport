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
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use FacturaScripts\Core\Model\User;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Model\Empresa;

/**
 * Class for default|common data for PDF Template.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ReportDefaultData
{
    /**
     * Additional data from developer for the report.
     *
     * @var array
     */
    public array $additional;

    /**
     *
     * @var Empresa
     */
    protected $company;

    /**
     * Current Page Number
     *
     * @var int
     */
    protected int $pageNum;

    /**
     * Settings for rendering
     *
     * @var array
     */
    protected array $renderCfg;

    /**
     * User logged in.
     *
     * @var User
     */
    protected $user;

    /**
     * Class constructor. Set user.
     *
     * @param User $user
     * @param Empresa $company
     */
    public function __construct(User $user, Empresa $company)
    {
        $this->additional = [];
        $this->renderCfg = [];

        $this->company = $company;
        $this->user = $user;
        $this->pageNum = 1;
    }

    /**
     * Increase the page counter.
     */
    public function addPage(): void
    {
        ++$this->pageNum;
    }

    /**
     * Get the value of the indicated field from the company.
     *
     * @param string $field
     * @return string
     */
    public function getCompanyFieldValue(string $field): string
    {
        $result = $this->company->{$field} ?? '';
        return match ($field) {
            'nombre' => empty($result) ? Tools::trans('all-companies') : $result,
            default => $result,
        };
    }

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function getPageNum(): int
    {
        return $this->pageNum;
    }

    /**
     * Return render configuration.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getRenderCfg(string $key, mixed $default = null): mixed
    {
        return $this->renderCfg[$key] ?? $default;
    }

    /**
     * Get the value of the indicated field from the user.
     *
     * @param string $field
     * @return string
     */
    public function getUserFieldValue(string $field): string
    {
        return $this->user->{$field} ?? '';
    }

    /**
     * Set new company data.
     *
     * @param Empresa $company
     */
    public function setCompany(Empresa $company): void
    {
        $this->company = $company;
    }

    /**
     * Set the page num to indicated number.
     *
     * @param int $pageNum
     */
    public function setPageNum(int $pageNum): void
    {
        $this->pageNum = $pageNum;
    }

    /**
     * Add an array of configuration values to render configuration.
     *
     * @param array $cfg
     * @return void
     */
    public function setRenderCfg(array $cfg): void
    {
        $this->renderCfg = array_merge($this->renderCfg, $cfg);
    }
}
