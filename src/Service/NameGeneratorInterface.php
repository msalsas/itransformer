<?php

namespace App\Service;

use App\Repository\ImageRepositoryInterface;

interface NameGeneratorInterface
{
    public function __construct(ImageRepositoryInterface $imageRepository);
    public function generate();
}