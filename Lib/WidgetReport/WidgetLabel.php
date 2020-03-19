<?php
/**
 * This file is part of ExtendedReport plugin for FacturaScripts.
 * FacturaScripts Copyright (C) 2018-2020 Carlos Garcia Gomez <carlos@facturascripts.com>
 * ExtendedReport Copyright (C) 2018-2020 Jose Antonio Cuello Principal <yopli2000@gmail.com>
 *
 * This program and its files are under the terms of the license specified in the LICENSE file.
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

    /**
     * Text alignment.
     *
     * @var string
     */
    protected $align;

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
     * @return string
     */
    private function getValue()
    {
        return $this->translate ? $this->toolBox()->i18n()->trans($this->value) : $this->value;
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
