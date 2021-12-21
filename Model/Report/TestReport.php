<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Model\Report;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Plugins\ExtendedReport\Model\Base\ModelReport;
use FacturaScripts\Plugins\ExtendedReport\Model\Report\Data\TestData;

/**
 * Class to manage and report test extended report
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class TestReport extends ModelReport
{

    /**
     * Class for set test data
     *
     * @var TestData
     */
    private $testData;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->testData = new TestData();
    }

    /**
     * Execute the load data for report.
     *
     * For get filter from controller:
     *     - $id = $filters['filter_name']->getValue();
     *
     * For get filter date period:
     *     - $startdate = $filters['filter_name']->getValue(PeriodFilter::STARTDATE_ID);
     *     - $enddate = $filters['filter_name']->getValue(PeriodFilter::ENDDATE_ID);
     *
     * @param BaseFilter[] $filters
     * @param DataBaseWhere[] $where
     * @param array $order
     * @param int $offset
     * @param int $limit
     * @return TestData[]
     */
    public function all($filters, $where, $order, $offset, $limit)
    {
        /// Get filters values example
        if (isset($filters['code'])) {
            $id = $filters['code']->getValue();

            /// Check values
            if (empty($id)) {
                $this->toolBox()->i18nLog()->warning('no-id-informed');
                return [];
            }
        }

        $result = $this->mainProcess();
        return $result;
    }

    /**
     * Process data to calculate report
     *
     * @return TestData[]
     */
    public function mainProcess(): array
    {
        $result = [];
        $total = \rand(5, 500);
        for ($index = 1; $index <= $total; ++$index) {
            $data = new TestData();
            $data->id = $index;
            $data->name = $this->testName();
            $data->date = $this->testDate();
            $data->total = $this->testTotal();
            $result[] = $data;
        }

        return $result;
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

    private function testTotal(): string
    {
        return \rand(0, 5000) . '.' . \rand(0, 99);
    }
}
