<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class BasicController extends BaseImageController
{
    /**
     * @Route("change-dimensions", name="changeDimensions", methods={"POST"})
     */
    public function changeDimensions()
    {
        return $this->findAndRenderImage(function($image) {
            $iNewWidth = (int) $this->request->request->get('dimensionesX');
            $iNewHeight = (int) $this->request->request->get('dimensionesY');

            return $this->imageTransformer->changeDimensions($image, $iNewWidth, $iNewHeight);
        });
    }

    /**
     * @Route("crop", name="crop", methods={"POST"})
     */
    public function crop()
    {
        return $this->findAndRenderImage(function($image) {
            $cropLeft = (int) $this->request->request->get('recortar_izq');
            $cropRight = (int) $this->request->request->get('recortar_der');
            $cropTop = (int) $this->request->request->get('recortar_arr');
            $cropBottom = (int) $this->request->request->get('recortar_aba');

            return $this->imageTransformer->crop($image, $cropTop, $cropRight, $cropBottom, $cropLeft);
        });
    }

    /**
     * @Route("change-brightness", name="changeBrightness", methods={"POST"})
     */
    public function changeBrightness()
    {
        return $this->findAndRenderImage(function($image) {
            $brightness = (int) $this->request->request->get('brillo');

            return $this->imageTransformer->changeBrightness($image, $brightness);
        });
    }

    /**
     * @Route("change-contrast", name="changeContrast", methods={"POST"})
     */
    public function changeContrast()
    {
        return $this->findAndRenderImage(function($image) {
            $contrast = (int) $this->request->request->get('contraste');

            return $this->imageTransformer->changeContrast($image, $contrast);
        });
    }

    /**
     * @Route("rotate", name="rotate", methods={"POST"})
     */
    public function rotate()
    {
        return $this->findAndRenderImage(function($image) {
            $rotation = (int) $this->request->request->get('rotacion');

            return $this->imageTransformer->rotate($image, $rotation);
        });
    }
}
