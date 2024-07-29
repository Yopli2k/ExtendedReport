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
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\ProductoImagen;
use FacturaScripts\Dinamic\Model\Variante;

/**
 * Class for display an image from the product in the report.
 *
 * @author Jose Antonio Cuello Principal <yopli2000@gmail.com>
 */
class WidgetImageproduct extends WidgetImage
{

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
        $productImage = $this->getProductImage();
        $file = $productImage->getFile();
        if (false === $file->exists()) {
            return;
        }

        $fileFull = $file->getFullPath();
        if (file_exists($fileFull)) {
            $this->renderImage($pdf, $fileFull, $posX, $posY, $width, $height);
        }
    }

    /**
     * @return ProductoImagen
     */
    private function getProductImage(): ProductoImagen
    {
        $variant = new Variante();
        $where = [ new DataBaseWhere('referencia', $this->value) ];
        if (false === $variant->loadFromCode('', $where)) {
            return new ProductoImagen();
        }

        $productImage = new ProductoImagen();
        $where = [
            new DataBaseWhere('idproducto', $variant->idproducto),
            new DataBaseWhere('referencia', $variant->referencia),
            new DataBaseWhere('referencia', null, 'IS', 'OR'),
        ];
        $productImage->loadFromCode('', $where, ['referencia' => 'DESC']);
        return $productImage;
    }
}
