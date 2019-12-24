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
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;
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

    public function testCropWithNumericValueShouldThrowError()
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
}