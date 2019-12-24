<?php

namespace Test\Service;

use App\Entity\Image;
use App\Repository\ImageRepositoryInterface;
use App\Service\FileUploader;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ImageUploaderTest extends WebTestCase
{
    const ORIGINAL_PATH = "tests/Mock/images/";
    const ORIGINAL_NAME_WITH_EXTENSION = "image0.png";
    const ORIGINAL_EXTENSION = "png";
    const ORIGINAL_HEIGHT = 5;
    const ORIGINAL_WIDTH = 5;
    const ORIGINAL_NUMBER = 0;
    const ORIGINAL_SIZE= 124;
    const ORIGINAL_NAME = "image0";
    const MIME_TYPE = "image/jpeg";

    /**
     * @var MockObject
     */
    protected $emMock;
    protected $uploadedFile;
    protected $session;
    protected $fileUploader;

    public function setUp()
    {
        parent::setUp();

        $this->session = new Session(new MockArraySessionStorage());
        $entityRepositoryMock = $this->createMock(ImageRepositoryInterface::class);
        $this->emMock = $this->createMock(EntityManagerInterface::class);
        $this->fileUploader = $this->createMock(FileUploader::class);

        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->willReturn(self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION);
        $this->emMock->expects($this->any())
            ->method('persist');
        $this->emMock->expects($this->any())
            ->method('flush');

        $this->emMock->expects($this->any())
            ->method('getRepository')
            ->willReturn($entityRepositoryMock);

        $this->uploadedFile = self::createUploadedFile();
    }

    public function testUploadShouldReturnImage()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertInstanceOf(Image::class, $image);
    }

    public function testUploadShouldReturnImageWithPath()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals(self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION, $image->getPath());
    }

    public function testUploadShouldReturnImageWithExtension()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals(self::ORIGINAL_EXTENSION, $image->getExtension());
    }

    public function testUploadShouldReturnImageWithHeight()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals(self::ORIGINAL_HEIGHT, $image->getHeight());
    }

    public function testUploadShouldReturnImageWithWidth()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals(self::ORIGINAL_HEIGHT, $image->getWidth());
    }


//    public function testUploadShouldReturnImageWithName()
//    {
//        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);
//
//        $image = $imageUploader->upload($this->uploadedFile, new Image());
//
//        $this->assertEquals(strtolower(self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION), $image->getName());
//    }

    public function testUploadShouldReturnImageWithNumber()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals(self::ORIGINAL_NUMBER, $image->getNumber());
    }

    public function testUploadShouldReturnImageWithSize()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals(self::ORIGINAL_SIZE, $image->getSize());
    }

    public function testUploadShouldReturnImageWithSessionId()
    {
        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $image = $imageUploader->upload($this->uploadedFile, new Image());

        $this->assertEquals($this->session->getId(), $image->getSessionId());
    }

    public function testShouldThrowFileExceptionWhenFileUploaderThrowsFileException()
    {
        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->will($this->throwException(new FileException));

        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $this->expectException(FileException::class);

        $imageUploader->upload($this->uploadedFile, new Image());
    }

    public function testShouldThrowExceptionWhenFileUploaderThrowsAnyOtherException()
    {
        $this->fileUploader->expects($this->once())
            ->method('upload')
            ->will($this->throwException(new \RuntimeException));

        $imageUploader = new ImageUploader($this->session, $this->emMock, $this->fileUploader);

        $this->expectException(\RuntimeException::class);

        $imageUploader->upload($this->uploadedFile, new Image());
    }

    protected static function createUploadedFile()
    {
        return new UploadedFile(self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION, self::ORIGINAL_NAME, self::MIME_TYPE, null, true);
    }
}