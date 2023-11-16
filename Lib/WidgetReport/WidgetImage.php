<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2023 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2023 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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

use Cezpdf;

/**
 * Class for display an image in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetImage extends WidgetItem
{

    /**
     * Image align.
     * Default center.
     *
     * @var string
     */
    protected $align;

    /**
     * Image angle rotation.
     * From 0 to 360.
     * Default 0.
     *
     * @var int
     */
    protected $angle;

    /**
     * Image padding.
     * Default 5.
     *
     * @var int
     */
    protected $padding;

    /**
     * @var string
     */
    protected $resize;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->align = $data['align'] ?? 'center';
        $this->angle = isset($data['angle']) ? (int)$data['angle'] : 0;
        $this->padding = isset($data['padding']) ? (int)$data['padding'] : 5;
        $this->resize = $data['resize'] ?? 'width';
    }

    /**
     * Add an Image to pdf document.
     *
     * @param Cezpdf $pdf
     * @param float $posX
     * @param float $posY
     * @param float $width
     */
    public function render(&$pdf, $posX, $posY, $width, $height)
    {
        if (empty($this->value)) {
            return;
        }

        try {
            $this->renderImage($pdf, $this->value, $posX, $posY, $height);
        } catch (Exception $ex) {
        }
    }

    /**
     * Render the image.
     *
     * @param Cezpdf $pdf
     * @param string $file
     * @param float $posX
     * @param float $posY
     * @param float $width
     * @param float $height
     */
    protected function renderImage(&$pdf, $file, $posX, $posY, $width, $height)
    {
        if (empty($file)) {
            return;
        }

        $imageInfo = getimagesize($file);
        if ($imageInfo === false) {
            return;
        }

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $pdf->addJpegFromFile($file, $posX, $posY - ($height - 15), $width, $height);
                break;

            case IMAGETYPE_PNG:
                $pdf->addPngFromFile($file, $posX, $posY - ($height - 15), $width, $height);
                break;

            case IMAGETYPE_GIF:
                $pdf->addGifFromFile($file, $posX, $posY - ($height - 15), $width, $height);
                break;
        }
    }
}
