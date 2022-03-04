<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2015-2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2021-2022 Jose Antonio Cuello Principal <yopli2000@gmail.com>
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
 * Class for displaying texts in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetLabel extends WidgetItem
{

    private const AUTO_TEXT_DATE = '[date]';
    private const AUTO_TEXT_DATETIME = '[datetime]';

    /**
     * Text alignment.
     *
     * @var string
     */
    protected $align;

    /**
     * The color for backgroud data.
     *
     * @var array
     */
    protected $bgcolor;

    /**
     * Indicates whether bold will be used.
     *
     * @var bool
     */
    protected $bold;

    /**
     * Indicates whether italic will be used.
     *
     * @var bool
     */
    protected $italic;

    /**
     * The size of the text font.
     *
     * @var int
     */
    protected $size;

    /**
     * Indicates whether to translate the value into the user's language.
     *
     * @var bool
     */
    protected $translate;

    /**
     * Indicates whether underline will be used.
     * @var bool
     */
    protected $underline;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->align = isset($data['align']) ? $data['align'] : 'left';
        $this->bold = isset($data['bold']) ? (bool) $data['bold'] : false;
        $this->italic = isset($data['italic']) ? (bool) $data['italic'] : false;
        $this->size = isset($data['size']) ? (int) $data['size'] : 10;
        $this->translate = isset($data['translate']) ? (bool) $data['translate'] : false;
        $this->underline = isset($data['underline']) ? (bool) $data['underline'] : false;

        $color = isset($data['bgcolor']) ? $data['bgcolor'] : 'white';
        $this->bgcolor = $this->rgbFromColorName($color);
    }

    /**
     * Add Label to pdf document.
     *
     * @param Cezpdf $pdf
     * @param float $posX
     * @param float $posY
     * @param float $width
     */
    public function render(&$pdf, $posX, $posY, $width)
    {
        $pdf->setColor($this->bgcolor['r'], $this->bgcolor['g'], $this->bgcolor['b']);
        $pdf->filledRectangle($posX, $posY, $width, 20);

        $pdf->setColor($this->color['r'], $this->color['g'], $this->color['b']);
        $pdf->addText(
            $posX,
            $posY,
            $this->size,
            $this->getText(),
            $width,
            $this->align);
    }

    /**
     * Get text with format properties.
     */
    private function getText()
    {
        $value = $this->toolBox()->utils()->fixHtml($this->getValue());
        $this->setFontStyle($value, $this->bold, 'u');
        $this->setFontStyle($value, $this->italic, 'i');
        $this->setFontStyle($value, $this->bold, 'b');
        return $value;
    }

    /**
     * Obtain the value to be represented.
     *
     * @return mixed
     */
    private function getValue()
    {
        switch ($this->value) {
            case self::AUTO_TEXT_DATE:
                return date('d-m-Y');

            case self::AUTO_TEXT_DATETIME:
                return date('d-m-Y H:i:s');

            default:
                return $this->translate ? $this->toolBox()->i18n()->trans($this->value) : $this->value;
        }
    }

    /**
     * Apply font style to value.
     *
     * @param string $value
     * @param bool   $apply
     * @param string $style
     */
    private function setFontStyle(&$value, $apply, $style)
    {
        if ($apply) {
            $value = '<' . $style . '>' . $value . '</' . $style . '>';
        }
    }
}
