<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

/**
 * Specific band for the report header data.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandHeader extends BandItem
{

    /**
     *
     * @var bool
     */
    public $forceNewPage;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->forceNewPage = $data['newpage'] ?? false;
    }
}
