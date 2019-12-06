<?php

namespace App\Service;

use App\Entity\ImageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ImageUploader
{
    private $entityManager;
    private $sessionId;

    /**
     * @var FileUploader
     */
    private $fileUploader;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager, FileUploader $fileUploader)
    {
        $this->sessionId = $session->getId();
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
    }

    public function upload(UploadedFile $uploadedFile, ImageInterface $image)
    {
        try {
            list($width, $height) = @getimagesize($uploadedFile->getRealPath());
            $size = $uploadedFile->getSize();

            $path = $this->fileUploader->upload($uploadedFile);
            $nameWithExtension = trim(strtolower(substr($path, strpos($path, '/') + 1)));
            $name = substr($nameWithExtension, 0, strpos($nameWithExtension, '.'));
            $extension = substr($nameWithExtension, strpos($nameWithExtension, '.') + 1);

            $image->setPath($path);
            $image->setName($name);
            $image->setWidth($width);
            $image->setHeight($height);
            $image->setSize($size);
            $image->setExtension($extension);
            $image->setSessionId($this->sessionId);

            $this->save($image);

        } catch (FileException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

        return $image;
    }

    public function save(ImageInterface $image)
    {
        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }
}