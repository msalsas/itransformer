<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class FiltersController extends BaseImageController
{
    /**
     * @Route("gray-scale", name="grayScale", methods={"POST"})
     */
    public function grayScale()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->grayScale($image);
        });
    }

    /**
     * @Route("negate", name="negate", methods={"POST"})
     */
    public function negate()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->negate($image);
        });
    }

    /**
     * @Route("edge-detection", name="edgeDetection", methods={"POST"})
     */
    public function edgeDetection()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->edgeDetection($image);
        });
    }

    /**
     * @Route("emboss", name="emboss", methods={"POST"})
     */
    public function emboss()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->emboss($image);
        });
    }

    /**
     * @Route("mean-removal", name="meanRemoval", methods={"POST"})
     */
    public function meanRemoval()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->meanRemoval($image);
        });
    }

    /**
     * @Route("blur", name="blur", methods={"POST"})
     */
    public function blur()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->blur($image);
        });
    }

    /**
     * @Route("gaussian-blur", name="gaussianBlur", methods={"POST"})
     */
    public function gaussianBlur()
    {
        return $this->findAndRenderImage(function($image) {
            return $this->imageTransformer->gaussianBlur($image);
        });
    }

    /**
     * @Route("smooth", name="smooth", methods={"POST"})
     */
    public function smooth()
    {
        return $this->findAndRenderImage(function($image) {
            $smooth = (int) $this->request->request->get('suavizado');

            return $this->imageTransformer->smooth($image, $smooth);
        });
    }
}