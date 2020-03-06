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
 * Description of ConfigItem
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ConfigItem
{

    /**
     *
     * @var array
     */
    public $page = [];

    /**
     *
     * @var array
     */
    public $font = [];

    /**
     *
     * @var array
     */
    public $default = [];

    /**
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->loadPageConfig($data);
        $this->loadFontConfig($data);
        $this->loadDefaultConfig($data);
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

    /**
     * Load default configuration from array
     *
     * @param array $data
     */
    protected function loadDefaultConfig($data)
    {
        $this->default['group'] = $data['default']['group'] ?? 'main';
        $this->default['alter'] = $data['default']['alter'] ?? 'main';
    }
}
