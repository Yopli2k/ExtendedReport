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
 * Specific band for the report detail data.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandDetail extends BandItem
{

    /**
     * Name of the field that determines the break in the data sequence.
     *
     * @var string
     */
    public $fieldName;

    /**
     * Last value for fieldName.
     *
     * @var string
     */
    public $fieldValue = null;

    /**
     * Class constructor. Get initial values from param array.
     *
     * @param type $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->fieldName = $data['fieldname'] ?? '';
    }

    /**
     * Determine if there is a break in the sequence detail data.
     *
     * @param object $data
     * @param bool   $update
     * @return bool
     */
    public function hasFieldRupture($data, $update = false): bool
    {
        if (empty($this->fieldName)) {
            return false;
        }

        try {
            $value = $data->{$this->fieldName};
            if ($this->fieldValue == null) {
                $this->fieldValue = $value;
                return false;
            }

            $result = ($this->fieldValue != $value);
            if ($update && $result) {
                $this->fieldValue = $value;
            }
        } catch (\Exception $exc) {
            $result = false;
        }

        return $result;
    }
}
