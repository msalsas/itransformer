<?php

namespace Test\Service;

use App\Repository\ImageRepositoryInterface;
use App\Service\NameGenerator;
use Symfony\Bundle\FrameworkBundle\Tests\Functional\WebTestCase;

class NameGeneratorTest extends WebTestCase
{
    const IMAGE_0 = "image0";
    const IMAGE_1 = "image1";
    const IMAGE_11 = "image11";
    const IMAGE_101 = "image101";
    const IMAGE = "image";

    protected $entityRepositoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->entityRepositoryMock = $this->createMock(ImageRepositoryInterface::class);
    }

    public function testGenerateShouldReturnNameImage0()
    {
        $this->entityRepositoryMock->expects($this->once())
            ->method('findByName')
            ->willReturn(null);

        $nameGenerator = new NameGenerator($this->entityRepositoryMock);

        $name = $nameGenerator->generate();
        $this->assertEquals(self::IMAGE_0, $name);
    }

    public function testGenerateShouldReturnNameImage1()
    {
        $this->entityRepositoryMock->expects($this->exactly(2))
            ->method('findByName')
            ->willReturn(self::IMAGE, null);

        $nameGenerator = new NameGenerator($this->entityRepositoryMock);

        $name = $nameGenerator->generate();
        $this->assertEquals(self::IMAGE_1, $name);
    }

    public function testGenerateShouldReturnNameImage11()
    {
        $this->entityRepositoryMock->expects($this->exactly(12))
            ->method('findByName')
            ->willReturn(self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, null);

        $nameGenerator = new NameGenerator($this->entityRepositoryMock);

        $name = $nameGenerator->generate();
        $this->assertEquals(self::IMAGE_11, $name);
    }

    public function testGenerateShouldReturnNameImage1001()
    {
        $this->entityRepositoryMock->expects($this->exactly(102))
            ->method('findByName')
            ->willReturn(
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE, self::IMAGE,
                self::IMAGE, null);

        $nameGenerator = new NameGenerator($this->entityRepositoryMock);

        $name = $nameGenerator->generate();
        $this->assertEquals(self::IMAGE_101, $name);
    }
}