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

    const WRINKLED_PAPER = __DIR__ . '/../../public/img/wrinkledPaper.png';
    const OLD = __DIR__ . '/../../public/img/old.png';
    const FIRE = __DIR__ . '/../../public/img/fire.png';
    const DROPS = __DIR__ . '/../../public/img/drops.png';
    const LIGHTS = __DIR__ . '/../../public/img/lights.png';
    const COLORS = __DIR__ . '/../../public/img/colors.png';
    const COOL = __DIR__ . '/../../public/img/cool.png';
    const HORIZONTAL_FRAME = __DIR__ . '/../../public/img/horizontalFrame.png';
    const VERTICAL_FRAME = __DIR__ . '/../../public/img/verticalFrame.png';

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
        $this->throwErrorUnlessInteger($width, 1, 6000, "width");
        $this->throwErrorUnlessInteger($height, 1, 6000, "height");

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
        $this->throwErrorUnlessInteger($brightness, -255, 255, "brightness");

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
        $this->throwErrorUnlessInteger($contrast, -1000, 1000, "contrast");

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
        $this->throwErrorUnlessInteger($degrees, 0, 360, "degrees");

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

    /**
     * @param $image ImageInterface
     * @param $smooth integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function smooth(ImageInterface $image, $smooth)
    {
        $this->throwErrorUnlessInteger($smooth, -5000, 5000, "smooth");

        return $this->applyFilter($image, IMG_FILTER_SMOOTH, $smooth);
    }

    /**
     * @param $image ImageInterface
     * @param $pixelate integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function pixelate(ImageInterface $image, $pixelate)
    {
        $this->throwErrorUnlessInteger($pixelate, 0, 5000, "pixelate");

        return $this->applyFilter($image, IMG_FILTER_PIXELATE, $pixelate);
    }

    /**
     * @param $image ImageInterface
     * @param $matrix array
     * @param $divisor integer
     * @param $offset integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function convolution(ImageInterface $image, $matrix, $divisor, $offset)
    {
        if (!is_array($matrix)) {
            throw new ImageTransformerException("Matrix must be an array.");
        }
        foreach ($matrix as $array) {
            foreach ($array as $value) {
                $this->throwErrorUnlessInteger($value, -255, 255, "all matrix values");
            }
        }
        $this->throwErrorUnlessInteger($divisor, -255, 1000, "divisor");
        $this->throwErrorUnlessInteger($offset, -1000, 1000, "divisor");

        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imageconvolution($canvas , $matrix,  $divisor,  $offset);

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @param $input integer
     * @param $output integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function gammaCorrection(ImageInterface $image, $input, $output)
    {
        $this->throwErrorUnlessInteger($input, 0, 50, "input");
        $this->throwErrorUnlessInteger($output, 0, 50, "output");

        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imagegammacorrect($canvas ,$input, $output);

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @param $red integer
     * @param $green integer
     * @param $blue integer
     * @param $alpha integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function colorize(ImageInterface $image, $red, $green, $blue, $alpha)
    {
        $this->throwErrorUnlessInteger($red, 0, 255, "red");
        $this->throwErrorUnlessInteger($green, 0, 255, "green");
        $this->throwErrorUnlessInteger($blue, 0, 255, "blue");
        $this->throwErrorUnlessInteger($alpha, 0, 127, "alpha");

        return $this->applyFilter($image, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
    }

    /**
     * @param $image ImageInterface
     * @param $redChecked integer
     * @param $greenChecked integer
     * @param $blueChecked integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function highlightColors(ImageInterface $image, $redChecked, $greenChecked, $blueChecked)
    {
        $this->throwErrorUnlessBoolean($redChecked, "red");
        $this->throwErrorUnlessBoolean($greenChecked, "green");
        $this->throwErrorUnlessBoolean($blueChecked, "blue");

        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        if ($redChecked) {
            for ($x = 0; $x < $image->getWidth(); $x++) {
                for ($y = 0; $y < $image->getHeight(); $y++) {

                    $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                    $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                    $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                    if ($red < 251 && $red > 240 && $blue + 20 < $red && $green + 20 < $red && $blue > 10 && $green > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red + 5, $green - 10, $blue - 10));
                    }
                    elseif ($red > 220 && $blue + 20 < $red && $green + 20 < $red && $red < 240 && $green > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red + 15, $green - 10, $blue - 10));
                    }
                    elseif ($blue + 20 < $red && $green + 20 < $red && $red < 220 && $green > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red + 30, $green - 10, $blue - 10));
                    }
                }

            }
        }
        if ($greenChecked) {
            for ($x = 0; $x < $image->getWidth(); $x++) {
                for ($y = 0; $y < $image->getHeight(); $y++) {
                    $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                    $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                    $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                    if ($green < 251 && $green > 240 && $blue + 20 < $green && $red + 20 < $green && $blue > 10 && $red > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 10, $green + 5, $blue - 10));
                    }
                    elseif ($green > 220 && $blue < $green && $red + 10 < $green && $green < 240 && $red > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 10, $green + 15, $blue - 10));
                    }
                    elseif ($blue < $green && $red + 10 < $green && $green < 220 && $red > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 10, $green + 30, $blue - 10));
                    }

                }
            }
        }
        if ($blueChecked) {
            for ($x = 0; $x < $image->getWidth(); $x++) {
                for ($y = 0; $y < $image->getHeight(); $y++) {
                    $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                    $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                    $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                    if ($blue < 251 && $blue > 240 && $green + 20 < $blue && $red + 20 < $blue && $blue > 10 && $red > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 10, $green - 10, $blue + 5));
                    }
                    elseif ($blue > 220 && $green < $blue && $red + 10 < $blue && $blue < 240 && $red > 10 && $green > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 10, $green - 10, $blue + 15));
                    }
                    elseif ($green < $blue && $red + 10 < $blue && $blue < 220 && $red > 10 && $green > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 10, $green - 10, $blue + 30));
                    }

                }
            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @param $redChecked integer
     * @param $greenChecked integer
     * @param $blueChecked integer
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function attenuateColors(ImageInterface $image, $redChecked, $greenChecked, $blueChecked)
    {
        $this->throwErrorUnlessBoolean($redChecked, "red");
        $this->throwErrorUnlessBoolean($greenChecked, "green");
        $this->throwErrorUnlessBoolean($blueChecked, "blue");

        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        if ($redChecked) {
            for ($x = 0; $x < $image->getWidth(); $x++) {
                for ($y = 0; $y < $image->getHeight(); $y++) {

                    $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                    $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                    $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                    if ($red < 251 && $red > 240 && $blue + 20 < $red && $green + 20 < $red && $blue > 10 && $green > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red - 40, $green, $blue));
                    }
                    elseif ($red > 220 && $blue + 20 < $red && $green + 20 < $red && $red < 240 && $green > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red -30, $green, $blue));
                    }
                    elseif ($blue + 20 < $red && $green + 20 < $red && $red < 220 && $red > 20 && $green > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red -20, $green, $blue));
                    }
                }

            }
        }
        if ($greenChecked) {
            for ($x = 0; $x < $image->getWidth(); $x++) {
                for ($y = 0; $y < $image->getHeight(); $y++) {
                    $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                    $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                    $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                    if ($green < 251 && $green > 240 && $blue + 20 < $green && $red + 20 < $green && $blue > 10 && $red > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red, $green - 40, $blue));
                    }
                    elseif ($green > 220 && $blue < $green && $red + 10 < $green && $green < 240 && $red > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red, $green - 30, $blue));
                    }
                    elseif ($blue < $green && $red + 10 < $green && $green < 220 && $green > 20 && $red > 10 && $blue > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red, $green - 20, $blue));
                    }

                }
            }
        }
        if ($blueChecked) {
            for ($x = 0; $x < $image->getWidth(); $x++) {
                for ($y = 0; $y < $image->getHeight(); $y++) {
                    $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                    $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                    $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                    if ($blue < 251 && $blue > 240 && $green + 20 < $blue && $red + 20 < $blue && $blue > 10 && $red > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red, $green, $blue - 40));
                    }
                    elseif ($blue > 220 && $green < $blue && $red + 10 < $blue && $blue < 240 && $red > 10 && $green > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red, $green, $blue - 30));
                    }
                    elseif ($green < $blue && $red + 10 < $blue && $blue < 220 && $blue > 20 && $red > 10 && $green > 10) {
                        imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $red, $green, $blue - 20));
                    }

                }
            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function superThinPencilEffect(ImageInterface $image)
    {
        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imagefilter($canvas, IMG_FILTER_EDGEDETECT);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $muchLighterGray = imagecolorallocate($canvas, 210, 210, 210);
        $lighterGray = imagecolorallocate($canvas, 175, 175, 175);
        $lightGray = imagecolorallocate($canvas, 140, 140, 140);
        $darkGray = imagecolorallocate($canvas, 105, 105, 105);
        $darkerGray = imagecolorallocate($canvas, 70, 70, 70);
        $muchDarkerGray = imagecolorallocate($canvas, 35, 35, 35);
        $black = imagecolorallocate($canvas, 0,0,0);
        for ($x=0; $x < $image->getWidth(); $x++) {
            for ($y=0; $y < $image->getHeight(); $y++) {
                $color = ImageColorAt($canvas, $x, $y);

                if (($color&0xFF) > 80) imagesetpixel($canvas, $x, $y, $white);
                elseif (($color&0xFF) > 75) imagesetpixel($canvas, $x, $y, $muchLighterGray);
                elseif (($color&0xFF) > 70) imagesetpixel($canvas, $x, $y, $lighterGray);
                elseif (($color&0xFF) > 65) imagesetpixel($canvas, $x, $y, $lightGray);
                elseif (($color&0xFF) > 60) imagesetpixel($canvas, $x, $y, $darkGray);
                elseif (($color&0xFF) > 60) imagesetpixel($canvas, $x, $y, $darkerGray);
                elseif (($color&0xFF) > 55) imagesetpixel($canvas, $x, $y, $muchDarkerGray);
                else imagesetpixel($canvas, $x, $y, $black);

            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function thinPencilEffect(ImageInterface $image)
    {
        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imagefilter($canvas, IMG_FILTER_EDGEDETECT);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $lighterGray = imagecolorallocate($canvas, 200, 200, 200);
        $lightGray = imagecolorallocate($canvas, 150, 150, 150);
        $darkGray = imagecolorallocate($canvas, 120, 120, 120);
        $darkerGray = imagecolorallocate($canvas, 80, 80, 80);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        for ($x=0; $x < $image->getWidth(); $x++) {
            for ($y=0; $y < $image->getHeight(); $y++) {
                $color = ImageColorAt($canvas, $x, $y);

                if (($color&0xFF) > 100) imagesetpixel($canvas, $x, $y, $white);
                elseif (($color&0xFF) > 95) imagesetpixel($canvas, $x, $y, $lighterGray);
                elseif (($color&0xFF) > 90) imagesetpixel($canvas, $x, $y, $lightGray);
                elseif (($color&0xFF) > 85) imagesetpixel($canvas, $x, $y, $darkGray);
                elseif (($color&0xFF) > 80) imagesetpixel($canvas, $x, $y, $darkerGray);
                else imagesetpixel($canvas, $x, $y, $black);

            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function regularPencilEffect(ImageInterface $image)
    {
        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imagefilter($canvas, IMG_FILTER_EDGEDETECT);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $lightGray = imagecolorallocate($canvas, 230, 230, 230);
        $darkGray = imagecolorallocate($canvas, 100, 100, 100);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        for ($x=0; $x < $image->getWidth(); $x++) {
            for ($y=0; $y < $image->getHeight(); $y++) {
                $color = ImageColorAt($canvas, $x, $y);

                if (($color&0xFF) > 120) imagesetpixel($canvas, $x, $y, $white);
                elseif (($color&0xFF) > 110) imagesetpixel($canvas, $x, $y, $lightGray);
                elseif (($color&0xFF) > 100) imagesetpixel($canvas, $x, $y, $darkGray);
                else imagesetpixel($canvas, $x, $y, $black);

            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function thickPencilEffect(ImageInterface $image)
    {
        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imagefilter($canvas, IMG_FILTER_EDGEDETECT);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        for ($x=0; $x < $image->getWidth(); $x++) {
            for ($y=0; $y < $image->getHeight(); $y++) {
                $color = ImageColorAt($canvas, $x, $y);

                if (($color&0xFF) > 120) imagesetpixel($canvas, $x, $y, $white);
                else imagesetpixel($canvas, $x, $y, $black);

            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function paintEffect(ImageInterface $image)
    {
        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        for ($x=0; $x < $image->getWidth(); $x++) {
            for ($y=0; $y < $image->getHeight(); $y++) {

                $red = (ImageColorAt($canvas, $x, $y) >> 16) & 0xFF;
                $green = (ImageColorAt($canvas, $x, $y) >> 8) & 0xFF;
                $blue = ImageColorAt($canvas, $x, $y) & 0xFF;

                if ($red > 200) $newRed = 230;
                elseif ($red > 150) $newRed = 180;
                elseif ($red > 100) $newRed = 130;
                elseif ($red > 50) $newRed = 80;
                else $newRed = 30;

                if ($green > 200) $newGreen = 230;
                elseif ($green > 150) $newGreen = 180;
                elseif ($green > 100) $newGreen = 130;
                elseif ($green > 50) $newGreen = 80;
                else $newGreen = 30;

                if ($blue > 200) $newBlue = 230;
                elseif ($blue > 150) $newBlue = 180;
                elseif ($blue > 100) $newBlue = 130;
                elseif ($blue > 50) $newBlue = 80;
                else $newBlue = 30;

                imagesetpixel($canvas, $x, $y, imagecolorallocate($canvas, $newRed, $newGreen, $newBlue));
            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function cheGuevaraEffect(ImageInterface $image)
    {
        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        imagefilter($canvas, IMG_FILTER_EDGEDETECT);
        $red = imagecolorallocate($canvas, 255, 0, 0);
        $black = imagecolorallocate($canvas, 0, 0, 0);
        for ($x=0; $x < $image->getWidth(); $x++) {
            for ($y=0; $y < $image->getHeight(); $y++) {
                $color = ImageColorAt($canvas, $x, $y);

                if ($color > 7900000) imagesetpixel($canvas, $x, $y,$red);
                else imagesetpixel($canvas, $x, $y,$black);
            }
        }

        return $this->createAndSaveNewImage($image, $canvas);
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function wrinkledPaperEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'wrinkledPaper');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function oldEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'old');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function fireEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'fire');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function dropsEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'drops');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function lightsEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'lights');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function colorsEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'colors');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function coolEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'cool');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function horizontalFrameEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'horizontal_frame');
    }

    /**
     * @param $image ImageInterface
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    public function verticalFrameEffect(ImageInterface $image)
    {
        return $this->overlapEffect($image, 'vertical_frame');
    }

    /**
     * @param $image ImageInterface
     * @param $overlapImageName string
     * @return ImageInterface
     * @throws ImageTransformerException
     */
    protected function overlapEffect(ImageInterface $image, $overlapImageName)
    {
        switch($overlapImageName)
        {
            case( 'wrinkledPaper' ):
                $overlapImage = self::WRINKLED_PAPER;
                break;
            case( 'old' ):
                $overlapImage = self::OLD;
                break;
            case( 'fire' ):
                $overlapImage = self::FIRE;
                break;
            case( 'drops' ):
                $overlapImage = self::DROPS;
                break;
            case( 'lights' ):
                $overlapImage = self::LIGHTS;
                break;
            case( 'colors' ):
                $overlapImage = self::COLORS;
                break;
            case( 'cool' ):
                $overlapImage = self::COOL;
                break;
            case( 'horizontal_frame' ):
                $overlapImage = self::HORIZONTAL_FRAME;
                break;
            case( 'vertical_frame' ):
                $overlapImage = self::VERTICAL_FRAME;
                break;
            default:
                throw new ImageTransformerException("Wrong overlap image name");
        }

        $canvas = $this->createCanvas($image);

        $canvas = $this->preserveTransparencyIfPng($image, $canvas);

        $canvas = $this->mergeImage($canvas, $image->getWidth(), $image->getHeight(), $overlapImage);

        return $this->createAndSaveNewImage($image, $canvas);
    }

    protected function throwErrorUnlessInteger($value, $min, $max, $valueName)
    {
        $valueName = ucfirst($valueName);
        if (!is_int($value)) {
            throw new ImageTransformerException("$valueName must be integer.");
        }
        if ($value < $min || $value > $max) {
            throw new ImageTransformerException("$valueName must be greater or equal than $min and less or equal than $max.");
        }
    }

    protected function throwErrorUnlessBoolean($value, $valueName)
    {
        $valueName = ucfirst($valueName);
        if (!is_bool($value)) {
            throw new ImageTransformerException("$valueName must be boolean.");
        }
    }

    protected function applyFilter(ImageInterface $image, $filterType, ...$args)
    {
        $canvas = $this->createCanvas($image);

        imagefilter($canvas, $filterType, ...$args);

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

    protected function mergeImage($canvas, $width, $height, $path)
    {
        $newCanvas = imagecreatefrompng($path);

        // Create blank canvas rescaled
        $newCanvasRescaled = imagecreatetruecolor($width, $height);

        $newCanvasRescaled = $this->preserveTransparency($newCanvasRescaled);

        // Copy original over the new blank canvas ($tmp)
        imagecopyresampled($newCanvasRescaled, $newCanvas, 0, 0, 0, 0, $width, $height, imagesx($newCanvas), imagesy($newCanvas));

        imagecopy($canvas, $newCanvasRescaled, 0, 0, 0, 0, $width, $height);

        return $canvas;
    }
}