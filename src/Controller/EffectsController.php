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
}