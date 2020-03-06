<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
        $array = static::loadXML('XMLReport', $name);
        if ($array === false) {
            return false;
        }

        $template->columns = [];
        $template->config = [];
        $template->name = $name;
        foreach ($array['children'] as $value) {
            switch ($value['tag']) {
                case 'columns':
                    static::setGroups($value['children'], $template->columns);
                    break;

                case 'config':
                    static::setConfig($value['children'], $template->config);
                    break;
            }
        }

        return true;
    }

    /**
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
        if (in_array($tag, ['page', 'font', 'default', 'header', 'detail', 'footer'])) {
            return $tag;
        }

        return parent::xmlToArrayAux($tag, $attributes);
    }

    /**
     * Load the report configuration from the ARRAY|JSON
     *
     * @param array $data
     * @param array $target
     */
    private static function setConfig($data, &$target)
    {
        $configClass = static::getNamespace() . 'ConfigItem';
        $target = new $configClass($data);
    }

    /**
     * Load the groups structure from the ARRAY|JSON
     *
     * @param array $data
     * @param array $target
     */
    private static function setGroups($data, &$target)
    {
        $groupClass = static::getNamespace() . 'GroupItem';
        foreach ($data as $item) {
            if ($item['tag'] === 'group') {
                $groupItem = new $groupClass($item);
                $target[$groupItem->name] = $groupItem;
            }
        }
    }
}
