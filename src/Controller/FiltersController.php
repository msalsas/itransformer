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
}