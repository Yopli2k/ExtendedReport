<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
 */
namespace FacturaScripts\Plugins\ExtendedReport\Lib\ExtendedReport;

use Cezpdf;

/**
 * Description of PDFCreator
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class PDFCreator
{
    /**
     * PDF object.
     *
     * @var Cezpdf
     */
    protected $pdf;

    public function __construct($type = 'A4', $orientation = 'portrait')
    {
        $this->pdf = new Cezpdf($type, $orientation);
        $this->pdf->addInfo('Creator', 'FacturaScripts');
        $this->pdf->addInfo('Producer', 'FacturaScripts');
        $this->pdf->tempPath = \FS_FOLDER . '/MyFiles/Cache';
    }
}
