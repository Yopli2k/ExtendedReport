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
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

/**
 * Contains the structure for general report settings.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ConfigItem
{

    /**
     * Default setup options.
     *
     * @var array
     */
    public $default = [];

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
        $this->loadDefaultConfig($data);
    }

    /**
     * Load default configuration from array
     *
     * @param array $data
     */
    protected function loadDefaultConfig(array $data)
    {
        $this->default['group'] = $data['group'] ?? 'main';
        $this->default['alter'] = $data['alter'] ?? 'other';
    }

    /**
     * Load page configuration from array
     *
     * @param array $data
     */
    protected function loadPageConfig(array $data)
    {
        $this->page['type'] = $data['page']['type'] ?? 'A4';
        $this->page['orientation'] = $data['page']['orientation'] ?? 'portrait';
    }

    /**
     * Load font configuration from array
     *
     * @param array $data
     */
    protected function loadFontConfig(array $data)
    {
        $this->font['type'] = $data['font']['type'] ?? 'Arial';
        $this->font['size'] = $data['font']['size'] ?? 12;
    }
}
