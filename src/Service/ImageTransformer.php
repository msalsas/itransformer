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

        imagedestroy($originalCanvas);

        $image->setWidth($width);
        $image->setHeight($height);

        return $this->createAndSaveNewImage($image, $newCanvas);
    }

    /**
     * @param $image ImageInterface
     * @param $brightness integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function changeBrightness(ImageInterface $image, $brightness)
    {
        if (!is_int($brightness)) {
            throw new ImageTransformerException("Brightness must be integers.");
        }
        if ($brightness < -255 || $brightness > 255) {
            throw new ImageTransformerException("Brightness must be greater than -256 and less than 256.");
        }

        return $this->applyFilter($image, IMG_FILTER_BRIGHTNESS, $brightness);
    }

    /**
     * @param $image ImageInterface
     * @param $contrast integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function changeContrast(ImageInterface $image, $contrast)
    {
        if (!is_int($contrast)) {
            throw new ImageTransformerException("Contrast must be integers.");
        }
        if ($contrast < -1000 || $contrast > 1000) {
            throw new ImageTransformerException("Contrast must be greater than -256 and less than 256.");
        }

        return $this->applyFilter($image, IMG_FILTER_CONTRAST, $contrast);
    }

    /**
     * @param $image ImageInterface
     * @param $top integer
     * @param $right integer
     * @param $bottom integer
     * @param $left integer
     * @return ImageInterface
     * @throws \Exception
     */
    public function crop(ImageInterface $image, $top, $right, $bottom, $left)
    {
        if (!is_int($top) || !is_int($right) || !is_int($bottom) || !is_int($left)) {
            throw new ImageTransformerException("Top, right, bottom and left must be integers.");
        }
        if ($top < 0 || $right < 0 || $top < 0 || $bottom < 0) {
            throw new ImageTransformerException("Top, right, bottom and left must be greater than 0.");
        }
        if (($left + $right) >= $image->getWidth() || ($top + $bottom) >= $image->getHeight()) {
            throw new ImageTransformerException("Invalid parameters.");
        }

        $originalCanvas = $this->createCanvas($image);

        $newWidth = $image->getWidth() - $left - $right;
        $newHeight = $image->getHeight() - $top - $bottom;
        $newCanvas = imagecreatetruecolor($newWidth, $newHeight);

        $newCanvas = $this->preserveTransparencyIfPng($image, $newCanvas);

        imagecopyresampled($newCanvas, $originalCanvas, 0, 0, $left, $top, $newWidth, $newHeight, $newWidth, $newHeight);

        imagedestroy($originalCanvas);

        $image->setWidth(floor($newWidth));
        $image->setHeight(floor($newHeight));

        return $this->createAndSaveNewImage($image, $newCanvas);
    }

    /**
     * @param $image ImageInterface
     * @param $degrees integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function rotate(ImageInterface $image, $degrees)
    {
        if (!is_int($degrees)) {
            throw new ImageTransformerException("Degrees must be integer.");
        }
        if ($degrees < 0 || $degrees > 360) {
            throw new ImageTransformerException("Degrees must be greater or equal than 0 and lower or equal than 360.");
        }

        $originalCanvas = $this->createCanvas($image);

        $newCanvas = imagerotate($originalCanvas, $degrees, 0);

        $newCanvas = $this->preserveTransparencyIfPng($image, $newCanvas);

        imagedestroy($originalCanvas);

        $image->setWidth(floor(imagesx($newCanvas)));
        $image->setHeight(floor(imagesy($newCanvas)));

        return $this->createAndSaveNewImage($image, $newCanvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function grayScale(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_GRAYSCALE);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function negate(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_NEGATE);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function edgeDetection(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_EDGEDETECT);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function emboss(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_EMBOSS);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function meanRemoval(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_MEAN_REMOVAL);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function blur(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_SELECTIVE_BLUR);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function gaussianBlur(ImageInterface $image)
    {
        return $this->applyFilter($image, IMG_FILTER_GAUSSIAN_BLUR);
    }

    protected function applyFilter(ImageInterface $image, $filterType, $value = 0)
    {
        $canvas = $this->createCanvas($image);

        imagefilter($canvas, $filterType, $value);

        return $this->createAndSaveNewImage($image, $canvas);
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

    protected function createAndSaveNewImage(ImageInterface $image, $canvas)
    {
        $newImage = clone $image;
        $this->setNewPath($newImage);
        $this->imageUploader->save($newImage);

        return self::createImage($newImage, $canvas);
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
        $image->setName($name);

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