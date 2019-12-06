<?php

namespace App\Entity;

interface ImageInterface
{
    public function getPath();

    /**
     * @param $path string
     */
    public function setPath($path);

    public function getName();

    /**
     * @param $name string
     */
    public function setName($name);

    public function getWidth();

    /**
     * @param $width string
     */
    public function setWidth($width);

    public function getHeight();

    /**
     * @param $height string
     */
    public function setHeight($height);

    public function getSize();

    /**
     * @param $size integer
     */
    public function setSize($size);

    public function getNumber();

    /**
     * @param $number integer
     */
    public function setNumber($number);

    public function getExtension();

    /**
     * @param $extension string
     */
    public function setExtension($extension);

    public function getSessionId();

    /**
     * @param $sessionId string
     */
    public function setSessionId($sessionId);
}