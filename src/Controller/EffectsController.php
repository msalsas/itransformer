<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class EffectsController extends BaseImageController
{
    /**
     * @Route("colorize", name="colorize", methods={"POST"})
     */
    public function colorize()
    {
        return $this->findAndRenderImage(function($image) {
            $red = (int) $this->request->request->get('colorear_r');
            $green = (int) $this->request->request->get('colorear_g');
            $blue = (int) $this->request->request->get('colorear_b');
            $alpha = (int) $this->request->request->get('alfa');

            return $this->imageTransformer->colorize($image, $red, $green, $blue, $alpha);
        });
    }

    /**
     * @Route("highlight-colors", name="highlightColors", methods={"POST"})
     */
    public function highlightColors()
    {
        return $this->findAndRenderImage(function($image) {
            $redInput = $this->request->request->get('resaltar_colores_r');
            $greenInput = $this->request->request->get('resaltar_colores_g');
            $blueInput = $this->request->request->get('resaltar_colores_b');

            $redChecked = $redInput === "check";
            $greenChecked = $greenInput === "check";
            $blueChecked = $blueInput === "check";

            return $this->imageTransformer->highlightColors($image, $redChecked, $greenChecked, $blueChecked);
        });
    }

    /**
     * @Route("attenuate-colors", name="attenuateColors", methods={"POST"})
     */
    public function attenuateColors()
    {
        return $this->findAndRenderImage(function($image) {
            $redInput = $this->request->request->get('atenuar_colores_r');
            $greenInput = $this->request->request->get('atenuar_colores_g');
            $blueInput = $this->request->request->get('atenuar_colores_b');

            $redChecked = $redInput === "check";
            $greenChecked = $greenInput === "check";
            $blueChecked = $blueInput === "check";

            return $this->imageTransformer->attenuateColors($image, $redChecked, $greenChecked, $blueChecked);
        });
    }
}