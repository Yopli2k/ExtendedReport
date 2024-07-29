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

use Cezpdf;
use Exception;

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
    protected int $angle;

    /**
     * Image padding.
     * Default 5.
     *
     * @var int
     */
    protected int $padding;

    /**
     * @var string
     */
    protected $resize;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct(array $data)
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
     * @param float $height
     */
    public function render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height)
    {
        if (empty($this->value)) {
            return;
        }

        try {
            $this->renderImage($pdf, $this->value, $posX, $posY, $width, $height);
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
    protected function renderImage(Cezpdf $pdf, string $file, float $posX, float $posY, float $width, float $height)
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
