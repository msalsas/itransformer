<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

interface ImageRepositoryInterface extends ServiceEntityRepositoryInterface
{
    public function find($id, $lockMode, $lockVersion);
    public function findByName($id);
}