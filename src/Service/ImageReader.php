<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageReader
{
    private $targetDirectory;
    private $defaultImagePath;

    public function __construct($targetDirectory, $defaultImage)
    {
        $this->targetDirectory = $targetDirectory;
        $this->defaultImagePath = $defaultImage;
    }

    public function read($path)
    {
        return $this->readFile($path);
    }

    public function readDefault()
    {
        return $this->readFile($this->getDefaultImage());
    }

    protected function readFile($fullPath)
    {
        $response = new BinaryFileResponse($fullPath);
        $response->headers->add(["Cache-Control" => "no-cache"]);
        $response->headers->add(["Expires" => "Sat, 26 Jul 1997 05:00:00 GMT"]); // Date in the past
        $response->headers->add(["Content-type" => "image/png"]);

        return $response;
    }

    private function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    private function getDefaultImage()
    {
        return $this->defaultImagePath;
    }
}