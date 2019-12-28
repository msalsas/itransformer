<?php

namespace App\Repository;

use App\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ImageRepository extends ServiceEntityRepository implements ImageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function find($sessionId, $lockMode = NULL, $lockVersion = NULL)
    {
        return $this->findOneBy(array('sessionId' => $sessionId), array('number' => 'DESC'));
    }

    public function findByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }
}