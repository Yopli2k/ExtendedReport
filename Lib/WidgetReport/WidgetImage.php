<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2026 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2026 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
use Throwable;

/**
 * Class for display an image in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetImage extends WidgetItem
{
    private const THUMBNAIL_PATH = '/MyFiles/Tmp/Thumbnails/';

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
     * @var bool
     */
    protected bool $resize;

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
        $this->resize = isset($data['resize']) ? $data['resize'] === 'true' : false;
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
            $fileName = ($this->resize === true)
                ? $this->getThumbnail($this->value, (int)$width, (int)$height)
                : $this->value;

            $this->renderImage($pdf, $fileName, $posX, $posY, $width, $height);
        } catch (Exception $ex) {
        }
    }

    /**
     * Return the widget data in a neutral structure, ready to be rendered as HTML.
     * The value holds the image source; resolving a server path to a public URL is
     * left to the render engine.
     *
     * @return array
     */
    public function toHtmlData(): array
    {
        $data = parent::toHtmlData();
        $data['tag'] = 'img';
        return $data;
    }

    /**
     * Convert an image to png format, keeping the alpha channel.
     * Return the new file path.
     *
     * @param string $file
     * @return string
     */
    protected function convertToPng(string $file): string
    {
        if (false === file_exists(FS_FOLDER . self::THUMBNAIL_PATH)) {
            mkdir(FS_FOLDER . self::THUMBNAIL_PATH, 0755, true);
        }

        $newFile = self::THUMBNAIL_PATH . pathinfo($file, PATHINFO_FILENAME) . '_webp.png';
        if (file_exists(FS_FOLDER . $newFile)) {
            return FS_FOLDER . $newFile;
        }

        try {
            $image = imagecreatefromstring(file_get_contents($file));
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagepng($image, FS_FOLDER . $newFile);
            imagedestroy($image);
        } catch (Throwable $th) {
            return '';
        }

        return FS_FOLDER . $newFile;
    }

    /**
     * Generate a thumbnail from the image.
     * Return the thumbnail file path.
     *
     * @param string $file
     * @param int $width
     * @param int $height
     * @return string
     */
    protected function getThumbnail(string $file, int $width, int $height): string
    {
        if (false === file_exists(FS_FOLDER . self::THUMBNAIL_PATH)) {
            mkdir(FS_FOLDER . self::THUMBNAIL_PATH, 0755, true);
        }

        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (false === in_array($ext, ['gif', 'jpeg', 'jpg', 'png', 'webp'])) {
            return '';
        }

        // rospdf no dibuja webp, la miniatura se guarda como png para conservar la transparencia
        $target = ($ext === 'webp') ? 'png' : $ext;
        $suffix = ($ext === 'webp') ? '_webp' : '';

        $thumbName = pathinfo($file, PATHINFO_FILENAME) . $suffix . '_' . $width . 'x' . $height . '.' . $target;
        $thumbFile = self::THUMBNAIL_PATH . $thumbName;
        if (file_exists(FS_FOLDER . $thumbFile)) {
            return FS_FOLDER . $thumbFile;
        }

        try {
            $image = imagecreatefromstring(file_get_contents($file));
            $imageWidth = imagesx($image);
            $imageHeight = imagesy($image);
            $ratio = $imageWidth / $imageHeight;
            if ($width / $height > $ratio) {
                $width = intval($height * $ratio);
            } else {
                $height = intval($width / $ratio);
            }
            $thumb = imagecreatetruecolor($width, $height);
            $this->keepAlphaChannel($thumb, $target, $width, $height);
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);

            switch ($target) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($thumb, FS_FOLDER . $thumbFile, 90);
                    break;

                case 'png':
                    imagepng($thumb, FS_FOLDER . $thumbFile);
                    break;

                case 'gif':
                    imagegif($thumb, FS_FOLDER . $thumbFile);
                    break;
            }

            imagedestroy($image);
            imagedestroy($thumb);
        } catch (Throwable $th) {
            return '';
        }

        return FS_FOLDER . $thumbFile;
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

        // rospdf no dibuja webp, lo convertimos a png antes de insertarlo
        if ($imageInfo[2] === IMAGETYPE_WEBP) {
            $file = $this->convertToPng($file);
            $imageInfo = empty($file) ? false : getimagesize($file);
            if ($imageInfo === false) {
                return;
            }
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

    /**
     * Fill the image with a transparent background, to keep the alpha channel on resize.
     *
     * @param resource|GdImage $image
     * @param string $ext
     * @param int $width
     * @param int $height
     */
    private function keepAlphaChannel($image, string $ext, int $width, int $height): void
    {
        if ($ext !== 'png') {
            return;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        imagefilledrectangle($image, 0, 0, $width, $height, $transparent);
    }
}
