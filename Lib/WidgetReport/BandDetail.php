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
 * Description of BandDetail
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class BandDetail extends BandItem
{

    /**
     *
     * @var string
     */
    public $fieldname;

    /**
     *
     * @var GroupItem
     */
    public $subgroup;

    /**
     *
     * @var string
     */
    public $subgroupValue;

    /**
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->fieldname = $data['fieldname'] ?? '';
        $this->subgroup = $data['subgroup'] ?? '';
        $this->subgroupValue = null;
    }

    /**
     * Determine if there is a break in the detail data.
     *
     * @param object $data
     * @param bool   $update
     * @return bool
     */
    public function hasDetailRupture($data, $update = false): bool
    {
        if (empty($this->subgroup) || empty($this->fieldname)) {
            return false;
        }

        try {
            $value = $data->{$this->fieldname};
            $result = ($this->subgroupValue != $value);
            if ($update && $result) {
                $this->subgroupValue = $value;
            }
        } catch (\Exception $exc) {
            $result = false;
        }

        return $result;
    }
}
