<?php

namespace Test\Service;

use App\Repository\ImageRepositoryInterface;
use App\Service\FileUploader;
use App\Service\NameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploaderTest extends WebTestCase
{
    const ORIGINAL_PATH = "tests/Mock/images/";
    const ORIGINAL_NAME_WITH_EXTENSION = "image0.png";
    const ORIGINAL_NAME = "image0";
    const TARGET_PATH = "tests/Mock/upload";
    const MIME_TYPE = "image/jpeg";
    const WRONG_TARGET_PATH = "////upload";

    /**
     * @var MockObject
     */
    protected $emMock;
    protected $entityRepositoryMock;
    protected $nameGenerator;
    protected $uploadedFile;

    public function setUp()
    {
        parent::setUp();

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
    }

    public function testUploadShouldUploadFile()
    {
        $fileUploader = new FileUploader(self::TARGET_PATH, $this->nameGenerator, $this->emMock);

        $filePathWithExtension = $fileUploader->upload($this->uploadedFile, $this->entityRepositoryMock);

        $this->assertEquals(self::TARGET_PATH . "/" .  self::ORIGINAL_NAME_WITH_EXTENSION, $filePathWithExtension);

        copy($filePathWithExtension, self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION);
        unlink($filePathWithExtension);
    }

    public function testUploadWithWrongTargetDirectoryShouldThrowError()
    {
        $fileUploader = new FileUploader(self::WRONG_TARGET_PATH, $this->nameGenerator, $this->emMock);

        $this->expectException(FileException::class);

        $fileUploader->upload($this->uploadedFile, $this->entityRepositoryMock);
    }

    protected static function createUploadedFile()
    {
        return new UploadedFile(self::ORIGINAL_PATH . self::ORIGINAL_NAME_WITH_EXTENSION, self::ORIGINAL_NAME, self::MIME_TYPE, null, true);
    }
}