<?php

namespace App\Service;

use App\Entity\ImageInterface;
use App\Exception\ImageTransformerException;

class ImageTransformer
{
    const JPG = "JPG";
    const JPEG = "JPEG";
    const PNG = "PNG";
    const WBMP = "WBMP";
    const GIF = "GIF";

    private $imageUploader;
    private $nameGenerator;

    public function __construct(ImageUploader $imageUploader, NameGeneratorInterface $nameGenerator)
    {
        $this->imageUploader = $imageUploader;
        $this->nameGenerator = $nameGenerator;
    }

    /**
     * @param $image ImageInterface
     * @param $width integer
     * @param $height integer
     * @return ImageInterface
     * @throws \Exception
     */
    public function changeDimensions(ImageInterface $image, $width, $height)
    {
        if (!is_int($width) || !is_int($height)) {
            throw new ImageTransformerException("Width and height must be integers.");
        }
        if ($width <= 0 || $width > 6000 || $height <= 0 || $height > 6000) {
            throw new ImageTransformerException("Width and height must be greater than 0 and less or equal than 6000.");
        }
        $originalCanvas = $this->createCanvas($image);

        $newCanvas = imagecreatetruecolor($width, $height);

        $newCanvas = $this->preserveTransparencyIfPng($image, $newCanvas);

        imagecopyresampled($newCanvas, $originalCanvas, 0, 0, 0, 0, $width, $height, $image->getWidth(), $image->getHeight());

        $image->setWidth($width);
        $image->setHeight($height);
        $this->setNewPath($image);
        $this->imageUploader->save($image);

        imagedestroy($originalCanvas);

        return self::createImage($image, $newCanvas);
    }

    /**
     * @param $image ImageInterface
     * @param $brightness integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function changeBright(ImageInterface $image, $brightness)
    {
        if (!is_int($brightness)) {
            throw new ImageTransformerException("Brightness must be integers.");
        }
        if ($brightness < -255 || $brightness > 255) {
            throw new ImageTransformerException("Brightness must be greater than -256 and less than 256.");
        }
        $canvas = $this->createCanvas($image);

        imagefilter($canvas, IMG_FILTER_BRIGHTNESS, $brightness);

        $this->setNewPath($image);

        return self::createImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @param $contrast integer
     * @return ImageInterface
     */
    public function changeContrast(ImageInterface $image, $contrast)
    {
        $canvas = $this->createCanvas($image);

        imagefilter($canvas, IMG_FILTER_CONTRAST, $contrast);

        $this->setNewPath($image);

        return self::createImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @param $top integer
     * @param $right integer
     * @param $bottom integer
     * @param $left integer
     */
    public function crop(ImageInterface $image, $top, $right, $bottom, $left)
    {
        // TODO: Crop image
    }

    /**
     * @param $image ImageInterface
     * @param $degrees integer
     */
    public function rotate(ImageInterface $image, $degrees)
    {
        // TODO: Rotate image
    }

    protected function preserveTransparencyIfPng(ImageInterface $image, $canvas)
    {
        if (self::isPng($image)) {
            $canvas = $this->preserveTransparency($canvas);
        }

        return $canvas;
    }

    /**
     * @param $canvas resource
     * @return resource
     */
    protected function preserveTransparency($canvas)
    {
        imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);

        return $canvas;
    }

    /**
     * @param $image ImageInterface
     * @return resource
     * @throws \Exception
     */
    protected function createCanvas(ImageInterface $image)
    {
        $path = $image->getPath();
        $extension = $image->getExtension();

        switch(strtoupper($extension)) {
            case self::JPEG:
            case self::JPG:
                $canvas = imagecreatefromjpeg($path);
                break;
            case self::PNG:
                $canvas = $this->preserveTransparency(imagecreatefrompng($path));
                break;
            case self::WBMP:
                $canvas = imagecreatefromwbmp($path);
                break;
            case self::GIF:
                $canvas = imagecreatefromgif($path);
                break;
            default:
                throw new \Exception("Extension not supported.");

        }

        return $canvas;
    }

    protected function getNewPath(ImageInterface $image)
    {
        $currentPath = $image->getPath();
        $currentNameWithExtension = $image->getName() . '.' . $image->getExtension();
        $currentDir = substr($currentPath, 0, strpos($currentPath, $currentNameWithExtension));
        $name = $this->nameGenerator->generate();

        return $currentDir . $name . '.' . $image->getExtension();
    }

    protected function setNewPath(ImageInterface $image)
    {
        $image->setNumber($image->getNumber() + 1);
        $image->setPath($this->getNewPath($image));
    }

    protected static function createImage(ImageInterface $image, $canvas, $quality = 100)
	{
        switch(strtoupper($image->getExtension())) {
            case self::JPEG:
            case self::JPG:
                imagejpeg($canvas, $image->getPath(), $quality);
                break;
            case self::PNG:
                imagealphablending($canvas, false);
                imagesavealpha($canvas, true);
                imagepng($canvas, $image->getPath());
                break;
            case self::WBMP:
                imagewbmp($canvas, $image->getPath(), $quality);
                break;
            case self::GIF:
                imagegif($canvas, $image->getPath(), $quality);
                break;
            default:
                throw new \Exception("Extension not supported.");
        }

        return $image;
	}

    protected static function isPng(ImageInterface $image)
    {
        return strtoupper($image->getExtension()) === self::PNG;
    }
}