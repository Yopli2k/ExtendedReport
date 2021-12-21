<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2020-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\PDFTemplate;

/**
 * Class for management of XML Report structure
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class ReportItemLoadEngine extends ItemLoadEngine
{

    /**
     * Loads an xmlreport data.
     *
     * @param string      $name
     * @param PDFTemplate $template
     *
     * @return boolean
     */
    public static function installXML($name, &$template)
    {
        $array = static::loadXML('Report', $name);
        if ($array === false) {
            return false;
        }

        $template->groups = [];
        $template->config = [];
        $template->name = $name;
        foreach ($array['children'] as $value) {
            switch ($value['tag']) {
                case 'group':
                    $template->groups[$value['name']] = static::groupFromArray($value);
                    break;

                case 'config':
                    $template->config = static::configFromArray($value);
                    break;
            }
        }

        return true;
    }

    /**
     * Returns the namespace used by the class.
     *
     * @return string
     */
    public static function getNamespace()
    {
        return parent::getNamespace() . 'WidgetReport\\';
    }

    /**
     *
     * @param string           $tag
     * @param SimpleXMLElement $attributes
     *
     * @return string
     */
    protected static function xmlToArrayAux($tag, $attributes): string
    {
        if (in_array($tag, ['page', 'font', 'default'])) {
            return $tag;
        }

        return parent::xmlToArrayAux($tag, $attributes);
    }

    /**
     * Create the report configuration from the ARRAY|JSON
     *
     * @param array $data
     * @return ConfigItem
     */
    private static function configFromArray($data)
    {
        $configClass = static::getNamespace() . 'ConfigItem';
        return new $configClass($data['children']);
    }

    /**
     * Load the groups structure from the ARRAY|JSON
     *
     * @param array $data
     * @return GroupItem
     */
    private static function groupFromArray($data)
    {
        $groupClass = static::getNamespace() . 'GroupItem';
        return new $groupClass($data);
    }
}
