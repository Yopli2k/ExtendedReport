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
 * Contains the structure for general report settings.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ConfigItem
{

    /**
     * Page setup options.
     *
     * @var array
     */
    public $page = [];

    /**
     * Font setup options.
     *
     * @var array
     */
    public $font = [];

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->loadPageConfig($data);
        $this->loadFontConfig($data);
    }

    /**
     * Load page configuration from array
     *
     * @param array $data
     */
    protected function loadPageConfig($data)
    {
        $this->page['type'] = $data['page']['type'] ?? 'A4';
        $this->page['orientation'] = $data['page']['orientation'] ?? 'portrait';
    }

    /**
     * Load font configuration from array
     *
     * @param array $data
     */
    protected function loadFontConfig($data)
    {
        $this->font['type'] = $data['font']['type'] ?? 'Arial';
        $this->font['size'] = $data['font']['size'] ?? 12;
    }
}
