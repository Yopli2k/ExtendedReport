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
use FacturaScripts\Core\Tools;

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
    protected string $align;

    /**
     * The color for background data.
     *
     * @var array
     */
    protected array $bgcolor;

    /**
     * Indicates whether bold will be used.
     *
     * @var bool
     */
    protected bool $bold;

    /**
     * Indicates whether italic will be used.
     *
     * @var bool
     */
    protected bool $italic;

    /**
     * Preserve line breaks in the HTML output (white-space: pre-line). Lets a
     * single multiline value (e.g. the filters block) stack its lines on screen.
     * Ignored by the PDF, which already wraps multiline values by itself.
     *
     * @var bool
     */
    protected bool $prewrap;

    /**
     * The size of the text font.
     *
     * @var int
     */
    protected int $size;

    /**
     * Indicates whether to translate the value into the user's language.
     *
     * @var bool
     */
    protected bool $translate;

    /**
     * Indicates whether underline will be used.
     * @var bool
     */
    protected bool $underline;

    /**
     * Class constructor. Load initials values from data array.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->align = $data['align'] ?? 'left';
        $this->bold = isset($data['bold']) && $data['bold'];
        $this->italic = isset($data['italic']) && $data['italic'];
        $this->prewrap = isset($data['prewrap']) && $data['prewrap'];
        $this->size = isset($data['size']) ? (int) $data['size'] : 10;
        $this->translate = isset($data['translate']) && $data['translate'];
        $this->underline = isset($data['underline']) && $data['underline'];

        $color = $data['bgcolor'] ?? false;
        $this->bgcolor = $color ? $this->rgbFromColor($color) : [];
    }

    /**
     * Obtain the value to be represented.
     *
     * @return mixed
     */
    public function getValue(): string
    {
        return $this->translate
            ? Tools::lang()->trans($this->value)
            : $this->value;
    }

    /**
     * Add Label to pdf document.
     *
     * @param Cezpdf $pdf
     * @param float $posX
     * @param float $posY
     * @param float $width
     * @param float $height
     */
    public function render(Cezpdf $pdf, float $posX, float $posY, float $width, float $height): void
    {
        $this->renderBackground($pdf, $posX, $posY, $width, $height);
        $color = $this->getColor();
        $pdf->setColor($color['r'], $color['g'], $color['b']);

        // Wrap the text for multiline
        $textHeight = $pdf->getFontHeight($this->size);
        $parts = preg_split('/$\R?^/m', $this->getText());
        foreach ($parts as $text) {
            do {
                $text = $pdf->addText($posX, $posY, $this->size, $text, $width, $this->align);
                $height -= $textHeight;
                if ($height < $textHeight) {
                    break 2;
                }
                $posY -= $textHeight;
            } while (false === empty($text));
        }
    }

    /**
     * Return the widget data in a neutral structure, ready to be rendered as HTML.
     * Adds alignment and font styles as CSS classes and color/background as inline
     * styles, keeping the value free of presentation markup.
     *
     * @return array
     */
    public function toHtmlData(): array
    {
        $data = parent::toHtmlData();
        $data['class'] = $this->htmlClasses();
        $data['style'] = $this->htmlStyles();
        return $data;
    }

    /**
     * Get the color for text data.
     * Use this method to allow override in child classes.
     *
     * @return array
     */
    protected function getColor(): array
    {
        return $this->color;
    }

    /**
     * Get text with format properties.
     */
    protected function getText(): string
    {
        $value = Tools::fixHtml($this->getValue());
        $this->setFontStyle($value, $this->underline, 'u');
        $this->setFontStyle($value, $this->italic, 'i');
        $this->setFontStyle($value, $this->bold, 'b');
        return $value;
    }

    /**
     * Build the list of CSS classes (alignment and font style) for HTML output.
     *
     * @return string
     */
    protected function htmlClasses(): string
    {
        $classes = [];
        if ($this->bold) {
            $classes[] = 'fw-bold';
        }
        if ($this->italic) {
            $classes[] = 'fst-italic';
        }
        if ($this->underline) {
            $classes[] = 'text-decoration-underline';
        }
        switch ($this->align) {
            case 'center':
                $classes[] = 'text-center';
                break;

            case 'right':
                $classes[] = 'text-end';
                break;
        }
        if ($this->cssClass !== '') {
            $classes[] = $this->cssClass;
        }
        return implode(' ', $classes);
    }

    /**
     * Build the inline CSS (text color and background) for HTML output.
     * The default black text color is omitted to keep the markup clean.
     *
     * @return string
     */
    protected function htmlStyles(): string
    {
        $styles = [];
        $color = $this->cssColor($this->getColor());
        if ($color !== '' && $color !== '#000000') {
            $styles[] = 'color:' . $color;
        }
        if (false === empty($this->bgcolor)) {
            $styles[] = 'background-color:' . $this->cssColor($this->bgcolor);
        }
        if ($this->prewrap) {
            $styles[] = 'white-space:pre-line';
        }
        return implode(';', $styles);
    }

    /**
     * Paint background rectangle.
     *
     * @param Cezpdf $pdf
     * @param float $posX
     * @param float $posY
     * @param float $width
     * @param float $height
     */
    protected function renderBackground(Cezpdf $pdf, float $posX, float $posY, float $width, float $height): void
    {
        if (empty($this->bgcolor)) {
            return;
        }

        $pdf->setColor($this->bgcolor['r'], $this->bgcolor['g'], $this->bgcolor['b']);
        $pdf->filledRectangle($posX-5, $posY-5, $width+5, $height+2);
    }

    /**
     * Apply font style to value.
     *
     * @param string $value
     * @param bool   $apply
     * @param string $style
     */
    private function setFontStyle(string &$value, bool $apply, string $style): void
    {
        if ($apply) {
            $value = '<' . $style . '>' . $value . '</' . $style . '>';
        }
    }
}
