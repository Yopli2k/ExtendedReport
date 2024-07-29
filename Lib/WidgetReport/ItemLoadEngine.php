<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2024 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2024 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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

use FacturaScripts\Core\Base\ToolBox;
use SimpleXMLElement;

/**
 * General class for loading an XML file with structures
 * by groups, columns and widgets.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
abstract class ItemLoadEngine
{

    /**
     *
     * @var string
     */
    private static $namespace = '\\FacturaScripts\\Dinamic\\Lib\\';

    /**
     * Process that converts the XML file into an object structure.
     */
    abstract public static function installXML($name, &$model);

    /**
     * Returns the namespace used by the class.
     *
     * @return string
     */
    public static function getNamespace()
    {
        return self::$namespace;
    }

    /**
     * Loads the XML file with the structure to use.
     *
     * @param string $folder
     * @param string $name
     * @return array|false
     */
    protected static function loadXML($folder, $name)
    {
        /// TODO: Set final folder with templates
        $fileName = \FS_FOLDER . '/Dinamic/XMLView/' . $folder . '/' . $name . '.xml';
        if (\FS_DEBUG && !file_exists($fileName)) {
            $fileName = \FS_FOLDER . '/Core/XMLView/' . $folder . '/' . $name . '.xml';
        }

        if (!file_exists($fileName)) {
            static::saveError('error-processing-xmlview', ['%fileName%' => $folder . '\\' . $name . '.xml']);
            return false;
        }

        $xml = simplexml_load_string(file_get_contents($fileName));
        if ($xml === false) {
            static::saveError('error-processing-xmlview', ['%fileName%' => $folder . '\\' . $name . '.xml']);
            return false;
        }

        return static::xmlToArray($xml);
    }

    /**
     * Generate the error message.
     *
     * @param string $message
     * @param array  $context
     */
    protected static function saveError($message, $context = [])
    {
        static::toolBox()->i18nLog()->critical($message, $context);
    }

    /**
     * Class with common tools.
     *
     * @return ToolBox
     */
    protected static function toolBox()
    {
        return new ToolBox();
    }

    /**
     * Turns an xml into an array.
     *
     * @param SimpleXMLElement $xml
     *
     * @return array
     */
    protected static function xmlToArray($xml): array
    {
        $array = [
            'tag' => $xml->getName(),
            'children' => [],
        ];

        /// attributes
        foreach ($xml->attributes() as $name => $value) {
            $array[$name] = (string) $value;
        }

        /// childs
        foreach ($xml->children() as $tag => $child) {
            $childAttr = $child->attributes();
            $name = static::xmlToArrayAux($tag, $childAttr);
            if ('' === $name) {
                $array['children'][] = static::xmlToArray($child);
                continue;
            }

            $array['children'][$name] = static::xmlToArray($child);
        }

        /// text
        $text = \trim((string) $xml);
        if ('' !== $text) {
            $array['text'] = $text;
        }

        return $array;
    }

    /**
     *
     * @param string           $tag
     * @param SimpleXMLElement $attributes
     *
     * @return string
     */
    protected static function xmlToArrayAux($tag, $attributes)
    {
        if (isset($attributes->name)) {
            return (string) $attributes->name;
        }

        if ($tag === 'row' && isset($attributes->type)) {
            return (string) $attributes->type;
        }

        return '';
    }
}
