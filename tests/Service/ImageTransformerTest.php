<?php

namespace Test\Service;

use App\Entity\Image;
use App\Exception\ImageTransformerException;
use App\Repository\ImageRepositoryInterface;
use App\Service\FileUploader;
use App\Service\ImageTransformer;
use App\Service\ImageUploader;
use App\Service\NameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ImageTransformerTest extends WebTestCase
{
    const ORIGINAL_PATH = "tests/Mock/images/";
    const ORIGINAL_NAME_WITH_EXTENSION = "image0.png";
    const CHANGED_DIMENSION_1_NAME_WITH_EXTENSION = "changedDimension1.png";
    const CHANGED_BRIGHTNESS_1_NAME_WITH_EXTENSION = "changedBrightness1.png";
    const CHANGED_CONTRAST_1_NAME_WITH_EXTENSION = "changedContrast1.png";
    const CROPPED_1_NAME_WITH_EXTENSION = "cropped1.png";
    const ROTATED_1_NAME_WITH_EXTENSION = "rotated1.png";
    const GRAY_SCALE_NAME_WITH_EXTENSION = "grayScale1.png";
    const NEGATE_NAME_WITH_EXTENSION = "negate1.png";
    const EDGE_DETECTION_NAME_WITH_EXTENSION = "edgeDetection1.png";
    const EMBOSS_NAME_WITH_EXTENSION = "emboss1.png";
    const MEAN_REMOVAL_NAME_WITH_EXTENSION = "meanRemoval1.png";
    const BLUR_NAME_WITH_EXTENSION = "blur1.png";
    const GAUSSIAN_BLUR_NAME_WITH_EXTENSION = "blurGauss1.png";
    const SMOOTH_NAME_WITH_EXTENSION = "smooth1.png";
    const PIXELATE_NAME_WITH_EXTENSION = "pixelate1.png";
    const CONVOLUTION_NAME_WITH_EXTENSION = "convolution1.png";
    const GAMMA_CORRECTION_NAME_WITH_EXTENSION = "gammaCorrection1.png";
    const COLORIZE_NAME_WITH_EXTENSION = "colorize1.png";
    const HIGHLIGHT_COLORS_NAME_WITH_EXTENSION = "highlightColors1.png";
    const ATTENUATE_COLORS_NAME_WITH_EXTENSION = "attenuateColors1.png";
    const SUPER_THIN_PENCIL_NAME_WITH_EXTENSION = "superThinPencil.png";
    const THIN_PENCIL_NAME_WITH_EXTENSION = "thinPencil.png";
    const REGULAR_PENCIL_NAME_WITH_EXTENSION = "regularPencil.png";
    const THICK_PENCIL_NAME_WITH_EXTENSION = "thickPencil.png";
    const PAINT_NAME_WITH_EXTENSION = "paint.png";
    const CHE_GUEVARA_NAME_WITH_EXTENSION = "cheGuevara.png";
    const WRINKLED_PAPER_COLORS_NAME_WITH_EXTENSION = "wrinkledPaper.png";
    const OLD_NAME_WITH_EXTENSION = "old.png";
    const FIRE_NAME_WITH_EXTENSION = "fire.png";
    const DROPS_NAME_WITH_EXTENSION = "drops.png";
    const LIGHTS_NAME_WITH_EXTENSION = "lights.png";
    const COLORS_NAME_WITH_EXTENSION = "colors.png";
    const COOL_NAME_WITH_EXTENSION = "cool.png";
    const HORIZONTAL_FRAME_NAME_WITH_EXTENSION = "horizontalFrame.png";
    const VERTICAL_FRAME_NAME_WITH_EXTENSION = "verticalFrame.png";

    const ORIGINAL_NAME = "image0";
    const ORIGINAL_EXTENSION = "png";
    const ORIGINAL_HEIGHT = 5;
    const ORIGINAL_WIDTH = 5;
    const ORIGINAL_NUMBER = 0;
    const ORIGINAL_SIZE= 124;
    const TARGET_PATH = "tests/Mock/upload";
    const COPY_PATH = "tests/Mock/images_copy";
    const TARGET_NAME_WITH_EXTENSION = "image1.png";
    const MIME_TYPE = "image/jpeg";
    const WRONG_TARGET_PATH = "////upload";

    /**
     * @var MockObject
     */
    protected $emMock;
    protected $entityRepositoryMock;
    protected $nameGenerator;
    protected $uploadedFile;
    /**
     * @var Session
     */
    protected $session;
    protected $fileUploader;
    protected $imageUploader;
    /**
     * @var ImageTransformer
     */
    protected $imageTransformer;

    public function setUp()
    {
        parent::setUp();

        $this->session = new Session(new MockArraySessionStorage());
        $this->entityRepositoryMock = $this->createMock(ImageRepositoryInterface::class);
        $this->emMock = $this->createMock(EntityManagerInterface::class);

        $this->entityRepositoryMock->expects($this->any())
            ->method('find')
            ->willReturn(null);
        $this->entityRepositoryMock->expects($this->any())
            ->method('findByName')
            ->willReturn(null);
        $this->emMock->expects($this->any())
            ->method('persist');
        $this->emMock->expects($this->any())
            ->method('flush');

        $this->emMock->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->entityRepositoryMock);

        $this->nameGenerator = new NameGenerator($this->entityRepositoryMock);
        $this->uploadedFile = self::createUploadedFile();

        $this->fileUploader = new FileUploader(self::TARGET_PATH, $this->nameGenerator, $this->emMock);
        $this->imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);
        $this->imageTransformer = new ImageTransformer($this->imageUploader, $this->nameGenerator);

        $this->fileUploader->upload($this->uploadedFile, $this->entityRepositoryMock);
    }

    public function tearDown()
    {
        parent::tearDown();

        copy(self::COPY_PATH . "/" .  self::ORIGINAL_NAME_WITH_EXTENSION, self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION);

        unlink(self::TARGET_PATH . "/" .  self::ORIGINAL_NAME_WITH_EXTENSION);
    }

    public function testChangeDimensions()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->changeDimensions($image, 3, 2);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::CHANGED_DIMENSION_1_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testChangeDimensionsWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->changeDimensions($image, "foo", 2);
    }

    public function testChangeDimensionsWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->changeDimensions($image, 6001, 2);
    }

    public function testChangeBrightness()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->changeBrightness($image, 3);


        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::CHANGED_BRIGHTNESS_1_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testChangeBrightnessWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->changeBrightness($image, "foo");
    }

    public function testChangeBrightnessWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->changeBrightness($image, 256);
    }

    public function testChangeContrast()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->changeContrast($image, 10);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::CHANGED_CONTRAST_1_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testChangeContrastWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->changeContrast($image, "foo");
    }

    public function testChangeContrastWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->changeContrast($image, 1001);
    }

    public function testCrop()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->crop($image, 1, 2, 1, 1);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::CROPPED_1_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testCropWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->crop($image, "foo", 2, 1, 1);
    }

    public function testCropWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->crop($image, -1, 2, 1, 1);
    }

    public function testRotate()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->rotate($image, 270);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::ROTATED_1_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testRotateWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->rotate($image, "foo");
    }

    public function testRotateWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->rotate($image, 361);
    }

    public function testGrayScale()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->grayScale($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::GRAY_SCALE_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testNegate()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->negate($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::NEGATE_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testEdgeDetection()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->edgeDetection($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::EDGE_DETECTION_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testEmboss()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->emboss($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::EMBOSS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testMeanRemoval()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->meanRemoval($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::MEAN_REMOVAL_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testBlur()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->blur($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::BLUR_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testGaussianBlur()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->gaussianBlur($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::GAUSSIAN_BLUR_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testSmooth()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->smooth($image, 500);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::SMOOTH_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testSmoothWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->smooth($image, "foo");
    }

    public function testSmoothWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->smooth($image, 5001);
    }

    public function testPixelate()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->pixelate($image, 100);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::PIXELATE_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testPixelateWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->pixelate($image, "foo");
    }

    public function testPixelateWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->pixelate($image, 5001);
    }

    public function testConvolution()
    {
        $image = $this->createImage();

        $matrix = self::getConvolutionMatrix();
        $imageTransformed = $this->imageTransformer->convolution($image, $matrix, 50, 100);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::CONVOLUTION_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testConvolutionWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $matrix = self::getConvolutionMatrix();
        $this->imageTransformer->convolution($image, $matrix, "foo", 100);
    }

    public function testConvolutionWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $matrix = self::getConvolutionOutOfRangeMatrix();
        $this->imageTransformer->convolution($image, $matrix, 100, 100);
    }

    public function testGammaCorrection()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->gammaCorrection($image, 20, 10);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::GAMMA_CORRECTION_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testGammaCorrectionWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->gammaCorrection($image, "foo", 50);
    }

    public function testGammaCorrectionWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->gammaCorrection($image, 51, 50);
    }

    public function testColorize()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->colorize($image, 20, 10, 30, 25);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::COLORIZE_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testColorizeWithNonNumericValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->colorize($image, "foo", 50, 20, 10);
    }

    public function testColorizeWithOutOfRangeValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->colorize($image, 256, 50, 10, 10);
    }

    public function testHighlightColors()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->highlightColors($image, true, true, true);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::HIGHLIGHT_COLORS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testHighlightColorsWithNonBooleanValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->highlightColors($image, "foo", true, false);
    }

    public function testAttenuateColors()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->attenuateColors($image, true, true, true);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::ATTENUATE_COLORS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testAttenuateColorsWithNonBooleanValueShouldThrowError()
    {
        $image = $this->createImage();

        $this->expectException(ImageTransformerException::class);

        $this->imageTransformer->attenuateColors($image, "foo", true, false);
    }

    public function testSuperThinPencilEffect()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->superThinPencilEffect($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::SUPER_THIN_PENCIL_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testThinPencilEffect()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->thinPencilEffect($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::THIN_PENCIL_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testRegularPencilEffect()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->regularPencilEffect($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::REGULAR_PENCIL_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testThickPencilEffect()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->thickPencilEffect($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::THICK_PENCIL_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testPaintEffect()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->paintEffect($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::PAINT_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testCheGuevaraEffect()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->cheGuevaraEffect($image);

        $this->assertFileEquals(self::ORIGINAL_PATH . '/' . self::CHE_GUEVARA_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectWrinkledPaper()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->wrinkledPaperEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::WRINKLED_PAPER_COLORS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectOld()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->oldEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::OLD_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectFire()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->fireEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::FIRE_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectDrops()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->dropsEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::DROPS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectLights()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->lightsEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::LIGHTS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectColors()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->colorsEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::COLORS_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectCool()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->coolEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::COOL_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectHorizontalFrame()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->horizontalFrameEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::HORIZONTAL_FRAME_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public function testOverlapEffectVerticalFrame()
    {
        $image = $this->createImage();

        $imageTransformed = $this->imageTransformer->verticalFrameEffect($image);

        $this->assertFileAlmostEquals(self::ORIGINAL_PATH . '/' . self::VERTICAL_FRAME_NAME_WITH_EXTENSION, $imageTransformed->getPath());
    }

    public static function assertFileAlmostEquals($expected, $actual, $message = '')
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        static::assertEquals(
            substr(current(unpack("h*", \file_get_contents($expected))), 0, 114),
            substr(current(unpack("h*", \file_get_contents($actual))), 0, 114),
            $message,
            0,
            10
        );
    }

    protected function createImage()
    {
        $image = new Image();
        $image->setSessionId($this->session->getId());
        $image->setPath(self::TARGET_PATH . "/" . self::ORIGINAL_NAME_WITH_EXTENSION);
        $image->setExtension(self::ORIGINAL_EXTENSION);
        $image->setHeight(self::ORIGINAL_HEIGHT);
        $image->setWidth(self::ORIGINAL_WIDTH);
        $image->setName(self::ORIGINAL_NAME);
        $image->setNumber(0);
        $image->setSize(self::ORIGINAL_SIZE);

        return $image;
    }

    protected static function createUploadedFile()
    {
        return new UploadedFile(self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION, self::ORIGINAL_NAME, self::MIME_TYPE, null, true);
    }

    protected static function getConvolutionMatrix()
    {
        return array(
            array(100, 100, 100),
            array(100, 100, 100),
            array(100, 100, 100),
        );
    }

    protected static function getConvolutionOutOfRangeMatrix()
    {
        return array(
            array(100, 100, 100),
            array(100, 100, 100),
            array(100, 100, 256),
        );
    }
}