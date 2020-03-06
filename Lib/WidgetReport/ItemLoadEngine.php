<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\WidgetReport;

use FacturaScripts\Core\Base\ToolBox;
use SimpleXMLElement;

/**
 * Description of ItemLoadEngine
 *
 * @author Artex Trading sa     <jcuello@artextrading.com>
 */
abstract class ItemLoadEngine
{

    /**
     *
     * @var string
     */
    private static $namespace = '\\FacturaScripts\\Dinamic\\Lib\\';

    abstract public static function installXML($name, &$model);

    /**
     *
     * @return string
     */
    public static function getNamespace()
    {
        return self::$namespace;
    }

    /**
     *
     * @param string $folder
     * @param string $name
     * @return array|false
     */
    protected static function loadXML($folder, $name)
    {
        $fileName = \FS_FOLDER . '/Dinamic/' . $folder . '/' . $name . '.xml';
        if (\FS_DEBUG && !file_exists($fileName)) {
            $fileName = \FS_FOLDER . '/Core/' . $folder . '/' . $name . '.xml';
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
     *
     * @param string $message
     * @param array  $context
     */
    protected static function saveError($message, $context = [])
    {
        static::toolBox()->i18nLog()->critical($message, $context);
    }

    /**
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
        $text = (string) $xml;
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
