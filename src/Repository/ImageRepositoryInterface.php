<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

interface ImageRepositoryInterface extends ServiceEntityRepositoryInterface
{
    public function find($id, $lockMode = null, $lockVersion = null);
    public function findByName($name);
}