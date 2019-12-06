<?php

namespace App\Service;

use App\Repository\ImageRepositoryInterface;

class NameGenerator implements NameGeneratorInterface
{
    const BASE_NAME = 'image';

    public function __construct(ImageRepositoryInterface $port)
    {
        $this->port = $port;
    }

    public function generate()
    {
        $imageName = self::BASE_NAME . '0';

        while($this->port->findByName($imageName)) {
            $imageNumber = intval(substr($imageName, 5));
            $imageNumber++;
            $imageName = self::BASE_NAME . $imageNumber;
        }

        return $imageName;
    }
}