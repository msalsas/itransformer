<?php
/*
 * Itransformer.es is an online application to transform images
Copyright (C) 2013  Manolo Salsas

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Contact: manolez@gmail.com - http://msalsas.com
* */


namespace MSD\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Imagen
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Imagen
{
	
	//private static $rutaImagenes = 'public/img/usuarios/';
	private static $rutaImagenes = '../usuarios/';
	private static $sizeMax = 3145728;
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ruta", type="string", length=255)
     */
    private $ruta;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * @var integer
     *
     * @ORM\Column(name="ancho", type="integer")
     */
    private $ancho;

    /**
     * @var integer
     *
     * @ORM\Column(name="alto", type="integer")
     */
    private $alto;

    /**
     * @var string
     *
     * @ORM\Column(name="formato", type="string", length=10)
     */
    private $formato;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="string", length=255)
     */
    private $error='';

    /**
     * @var integer
     *
     * @ORM\Column(name="numeroImagen", type="integer")
     */
    private $numeroImagen;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ruta
     *
     * @param string $ruta
     * @return Imagen
     */
    public function setRuta($ruta)
    {
        $this->ruta = $ruta;

        return $this;
    }

    /**
     * Get ruta
     *
     * @return string 
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Imagen
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Imagen
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set ancho
     *
     * @param integer $ancho
     * @return Imagen
     */
    public function setAncho($ancho)
    {
        $this->ancho = $ancho;

        return $this;
    }

    /**
     * Get ancho
     *
     * @return integer 
     */
    public function getAncho()
    {
        return $this->ancho;
    }

    /**
     * Set alto
     *
     * @param integer $alto
     * @return Imagen
     */
    public function setAlto($alto)
    {
        $this->alto = $alto;

        return $this;
    }

    /**
     * Get alto
     *
     * @return integer 
     */
    public function getAlto()
    {
        return $this->alto;
    }

    /**
     * Set formato
     *
     * @param string $formato
     * @return Imagen
     */
    public function setFormato($formato)
    {
        $this->formato = $formato;

        return $this;
    }

    /**
     * Get formato
     *
     * @return string 
     */
    public function getFormato()
    {
        return $this->formato;
    }

    /**
     * Set error
     *
     * @param string $error
     * @return Imagen
     */
    public function setError($error='')
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string 
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set numeroImagen
     *
     * @param integer $numeroImagen
     * @return Imagen
     */
    public function setNumeroImagen($numeroImagen)
    {
        $this->numeroImagen = $numeroImagen;

        return $this;
    }

    /**
     * Get numeroImagen
     *
     * @return integer 
     */
    public function getNumeroImagen()
    {
        return $this->numeroImagen;
    }
	
	
    /**
     * Get ruta_imagenes
     *
     * @return string 
     */
	public function getRutaImagenes()
	{
			return self::$rutaImagenes;
	}
	
	    /**
     * Get sizeMax
     *
     * @return string 
     */
	public function getSizeMax()
	{
			return self::$sizeMax;
	}
}
