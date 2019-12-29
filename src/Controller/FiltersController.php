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

    /**
     * @Route("pixelate", name="pixelate", methods={"POST"})
     */
    public function pixelate()
    {
        return $this->findAndRenderImage(function($image) {
            $pixelate = (int) $this->request->request->get('pixelacion');

            return $this->imageTransformer->pixelate($image, $pixelate);
        });
    }

    /**
     * @Route("convolution", name="convolution", methods={"POST"})
     */
    public function convolution()
    {
        return $this->findAndRenderImage(function($image) {
            $matrix = $this->getConvolutionMatrixFromRequest();
            $divisor = (int) $this->request->request->get('convolucion_divisor');
            $offset = (int) $this->request->request->get('convolucion_offset');

            return $this->imageTransformer->convolution($image, $matrix, $divisor, $offset);
        });
    }

    protected function getConvolutionMatrixFromRequest()
    {
        $matrix = array(array(3), array(3), array(3));
        for ($i=0; $i<=8; $i++) {
            $value = (int) $this->request->request->get('convolucion_matriz_'.$i);
            if ($i < 3) {
                $matrix[0][$i] = $value;
            } elseif ($i < 6) {
                $matrix[1][$i - 3] = $value;
            } elseif ($i < 9) {
                $matrix[2][$i - 6] = $value;
            }
        }

        return $matrix;
    }
}