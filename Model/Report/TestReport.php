<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts  Copyright (C) 2015-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport  Copyright (C) 2023-2023 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Model\Report;

use FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport\ModelReport;
use FacturaScripts\Plugins\ExtendedReport\Model\Report\Data\TestData;

/**
 * Class to manage and report test extended report
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class TestReport extends ModelReport
{

    /**
     * Load report data into array data property.
     *   - Set random test data records into data property.
     */
    public function loadData()
    {
        $records = \rand(5, 500);
        for ($index = 1; $index <= $records; ++$index) {
            $data = new TestData();
            $data->id = $index;
            $data->name = $this->testName();
            $data->date = $this->testDate();
            $data->amount = $this->testAmount();
            $this->data[] = $data;
        }
    }

    /**
     * Load report for column references.
     */
    public function loadDataColumns(int $max)
    {
        foreach (range(40, $max, 20) as $value) {
            $data = new TestData();
            $data->id = $value;
            $this->data[] = $data;
        }

    }

    private function testDate(): string
    {
        $day = \sprintf('%02d', \rand(1, 30));
        $mounth = \sprintf('%02d', \rand(1, 12));
        $year = \rand(1950, 2022);
        if ($mounth == '02' && intval($day) > 28) {
            $day = '28';
        }

        return $day . '-' . $mounth . '-' . $year;
    }

    private function testName(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $length = \rand(15, 80);

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[\rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function testAmount(): string
    {
        return \rand(0, 5000) . '.' . \rand(0, 99);
    }
}
