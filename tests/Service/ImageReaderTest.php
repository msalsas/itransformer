<?php

namespace Test\Service;

use App\Service\ImageReader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageReaderTest extends WebTestCase
{
    const ORIGINAL_NAME_WITH_EXTENSION = "image0.png";
    const TARGET_PATH = "tests/Mock/images";
    const CACHE_CONTROL = "cache-control";
    const NO_CACHE = "no-cache";
    const MIME_TYPE = "image/png";
    const CONTENT_TYPE = "content-type";
    const EXPIRES = "expires";
    const EXPIRES_DATE = "Sat, 26 Jul 1997 05:00:00 GMT";
    const DEFAULT_IMAGE_PATH = "tests/Mock/images/image0.png";
    const WRONG_TARGET_PATH = "tests/Mock/wrong";

    public function testReadShouldReturnBinaryFileResponse()
    {
        $imageReader = new ImageReader(self::TARGET_PATH, self::DEFAULT_IMAGE_PATH);

        $response = $imageReader->read(self::TARGET_PATH . '/' . self::ORIGINAL_NAME_WITH_EXTENSION);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    public function testReadShouldSetHeaders()
    {
        $imageReader = new ImageReader(self::TARGET_PATH, self::DEFAULT_IMAGE_PATH);

        $response = $imageReader->read(self::TARGET_PATH . '/' . self::ORIGINAL_NAME_WITH_EXTENSION);

        $headers = $response->headers->all();

        $this->assertArrayHasKey(self::CACHE_CONTROL, $headers);
        $this->assertArrayHasKey(0, $headers[self::CACHE_CONTROL]);
        $this->assertContains(self::NO_CACHE, $headers[self::CACHE_CONTROL][0]);

        $this->assertArrayHasKey(self::EXPIRES, $headers);
        $this->assertContains(self::EXPIRES_DATE, $headers[self::EXPIRES]);

        $this->assertArrayHasKey(self::CONTENT_TYPE, $headers);
        $this->assertContains(self::MIME_TYPE, $headers[self::CONTENT_TYPE]);

        $this->assertEquals(self::DEFAULT_IMAGE_PATH, $response->getFile()->getPathname());
    }

    public function testReadShouldReturnResponseWithFile()
    {
        $imageReader = new ImageReader(self::TARGET_PATH, self::DEFAULT_IMAGE_PATH);

        $response = $imageReader->read(self::TARGET_PATH . '/' . self::ORIGINAL_NAME_WITH_EXTENSION);

        $this->assertEquals(self::TARGET_PATH . '/' . self::ORIGINAL_NAME_WITH_EXTENSION, $response->getFile()->getPathname());
    }

    public function testReadDefaultShouldReturnResponseWithFile()
    {
        $imageReader = new ImageReader(self::TARGET_PATH, self::DEFAULT_IMAGE_PATH);

        $response = $imageReader->readDefault();

        $this->assertEquals(self::DEFAULT_IMAGE_PATH, $response->getFile()->getPathname());

    }

    public function testReadWrongFileShouldThrowError()
    {
        $imageReader = new ImageReader(self::WRONG_TARGET_PATH, self::DEFAULT_IMAGE_PATH);

        $this->expectException(FileException::class);

        $imageReader->read(self::ORIGINAL_NAME_WITH_EXTENSION);
    }
}