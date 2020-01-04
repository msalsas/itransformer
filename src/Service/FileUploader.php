<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var NameGeneratorInterface
     */
    private $nameGenerator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct($targetDirectory, NameGeneratorInterface $nameGenerator, EntityManagerInterface $entityManager)
    {
        $this->targetDirectory = $targetDirectory;
        $this->nameGenerator = $nameGenerator;
        $this->entityManager = $entityManager;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = $this->nameGenerator->generate();
        $fileNameWithExtension = $fileName . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileNameWithExtension);
        } catch (FileException $e) {
            // TODO handle exception if something happens during file upload
            throw $e;
        }

        return $this->getTargetDirectory() . '/' . $fileNameWithExtension;
    }

    public function delete($path)
    {
        unlink($path);
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}