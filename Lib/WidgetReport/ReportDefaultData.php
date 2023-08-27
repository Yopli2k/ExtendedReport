<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2015-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport  Copyright (C) 2023-2023 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use FacturaScripts\Dinamic\Model\User;
use FacturaScripts\Dinamic\Model\Empresa;

/**
 * Class for default|common data for PDF Template.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ReportDefaultData
{

    /**
     *
     * @var Empresa
     */
    public $company;

    /**
     * User logged in.
     *
     * @var User
     */
    public $user;

    /**
     * Current Page Number
     *
     * @var int
     */
    protected $pageNum;

    /**
     * Class constructor. Set user.
     *
     * @param User $user
     * @param Empresa $company
     */
    public function __construct(User $user, Empresa $company)
    {
        $this->company = $company;
        $this->user = $user;
        $this->pageNum = 1;
    }

    /**
     * Increase the page counter.
     */
    public function addPage()
    {
        ++$this->pageNum;
    }

    /**
     * Get current page number.
     *
     * @return int
     */
    public function getPageNum(): int
    {
        return $this->pageNum;
    }

    /**
     * Set the page num to indicated number.
     *
     * @param int $pageNum
     */
    public function setPageNum(int $pageNum)
    {
        $this->pageNum = $pageNum;
    }
}
