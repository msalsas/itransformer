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

    /**
     * @Route("super-thin-pencil-effect", name="superThinPencilEffect", methods={"POST"})
     */
    public function superThinPencilEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->superThinPencilEffect($image);
        });
    }

    /**
     * @Route("thin-pencil-effect", name="thinPencilEffect", methods={"POST"})
     */
    public function thinPencilEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->thinPencilEffect($image);
        });
    }

    /**
     * @Route("regular-pencil-effect", name="regularPencilEffect", methods={"POST"})
     */
    public function regularPencilEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->regularPencilEffect($image);
        });
    }

    /**
     * @Route("thick-pencil-effect", name="thickPencilEffect", methods={"POST"})
     */
    public function thickPencilEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->thickPencilEffect($image);
        });
    }

    /**
     * @Route("paint-effect", name="paintEffect", methods={"POST"})
     */
    public function paintEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->paintEffect($image);
        });
    }

    /**
     * @Route("che-guevara-effect", name="cheGuevaraEffect", methods={"POST"})
     */
    public function cheGuevaraEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->cheGuevaraEffect($image);
        });
    }

    /**
     * @Route("wrinkled-paper-effect", name="wrinkledPaperEffect", methods={"POST"})
     */
    public function wrinkledPaperEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->wrinkledPaperEffect($image);
        });
    }

    /**
     * @Route("old-effect", name="oldEffect", methods={"POST"})
     */
    public function oldEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->oldEffect($image);
        });
    }

    /**
     * @Route("fire-effect", name="fireEffect", methods={"POST"})
     */
    public function fireEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->fireEffect($image);
        });
    }

    /**
     * @Route("drops-effect", name="dropsEffect", methods={"POST"})
     */
    public function dropsEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->dropsEffect($image);
        });
    }

    /**
     * @Route("lights-effect", name="lightsEffect", methods={"POST"})
     */
    public function lightsEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->lightsEffect($image);
        });
    }

    /**
     * @Route("colors-effect", name="colorsEffect", methods={"POST"})
     */
    public function colorsEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->colorsEffect($image);
        });
    }

    /**
     * @Route("cool-effect", name="coolEffect", methods={"POST"})
     */
    public function coolEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->coolEffect($image);
        });
    }

    /**
     * @Route("horizontal-frame-effect", name="horizontalFrameEffect", methods={"POST"})
     */
    public function horizontalFrameEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->horizontalFrameEffect($image);
        });
    }

    /**
     * @Route("vertical-frame-effect", name="verticalFrameEffect", methods={"POST"})
     */
    public function verticalFrameEffect()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->verticalFrameEffect($image);
        });
    }
}