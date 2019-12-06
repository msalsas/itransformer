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


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Image
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 */
class Image implements ImageInterface
{
    const JPG = "JPG";
    const JPEG = "JPEG";
    const PNG = "PNG";
    const WBMP = "WBMP";
    const GIF = "GIF";

    private static $imagesPath = '../usuarios/';
    private static $sizeMax = 3145728;
    private static $WRINKLED_PAPER = '../img/papel_arr2.png';
    private static $OLD = '../img/antiguo.png';
    private static $FIRE = '../img/fuego.png';
    private static $DROPS = '../img/gotas.png';
    private static $LIGHTS = '../img/luces.png';
    private static $COLORS = '../img/colores.png';
    private static $COOL = '../img/molon.png';
    private static $HORIZONTAL_FRAME = '../img/marco_horizontal.png';
    private static $VERTICAL_FRAME = '../img/marco_vertical.png';
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
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="integer")
     */
    private $width;

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer")
     */
    private $height;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=10)
     */
    private $extension;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="string", length=255)
     */
    private $error='';

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255)
     */
    private $sessionId;

    public function filterImage( $value='', $filter, $limitUp=0, $limitDown=0 ) {

        if( preg_match( '/^[0-9]{1,2}$/', $filter ) )
        {
            //if has value
            if( is_numeric($value) || $limitUp || $limitDown )
            {
                //validate parameters
                if( is_numeric($value) && $value<=$limitUp && $value>=$limitDown )
                {
                    //create new and original canvas
                    if( $newCanvas = $this->createCanvas() )
                    {

                        //preserve transparency
                        if( $this->extension === "png" )
                        {
                            $newCanvas = $this->preserveTransparency( $newCanvas );
                        }

                        imagefilter( $newCanvas, $filter, $value );

                        $this->setError( '' );

                        return $newCanvas;
                    }
                    else
                    {
                        $this->setError( "No se pudo crear la imagen" );
                    }
                }
                else
                {
                    if( $filter == IMG_FILTER_BRIGHTNESS ) $this->setError( 'El brillo debe ser un número entre -255 y 255' );
                    elseif( $filter == IMG_FILTER_CONTRAST ) $this->setError( 'El contraste debe ser un número entre -1000 y 1000' );
                    elseif( $filter == IMG_FILTER_SMOOTH ) $this->setError( 'El suavizado debe ser un número entre -5000 y 5000' );
                    elseif( $filter == IMG_FILTER_PIXELATE ) $this->setError( 'El tamaño de bloque debe ser un número entre 0 y 1000000' );

                }

                //if doesn't have value
            } else {
                //create new and original canvas
                if( $newCanvas = $this->createCanvas() )
                {

                    //preserve transparency
                    if( $this->extension === "png" )
                    {
                        $newCanvas = $this->preserveTransparency( $newCanvas );
                    }

                    imagefilter( $newCanvas, $filter );

                    $this->setError( '' );

                    return $newCanvas;
                }
                else
                {
                    $this->setError( "No se pudo crear la imagen" );
                }
            }
        }
    }

    public function convolutionFilterImage( $matrix, $divisor, $offset )
    {
        //validate parameters
        if( is_numeric($divisor) && $divisor<=1000 && $divisor>=-1000 )
        {
            if( is_numeric($offset) && $offset<=1000 && $offset>=-1000 )
            {
                for($i=0;$i<=8;$i++) {
                    if($i<3) $value = $matrix[0][$i];
                    elseif($i<6) $value = $matrix[1][$i-3];
                    elseif($i<9) $value = $matrix[2][$i-6];
                    if( !is_numeric($value) || $value>255 || $value<-255 ) {
                        $this->setError('Los valores de la matriz deben ser números entre -255 y +255');
                        return;
                    }
                }

                //create new and original canvas
                if( $newCanvas = $this->createCanvas() )
                {

                    //preserve transparency
                    if( $this->extension === "png" )
                    {
                        $newCanvas = $this->preserveTransparency( $newCanvas );
                    }

                    imageconvolution($newCanvas , $matrix,  $divisor,  $offset );

                    $this->setError( '' );

                    return $newCanvas;
                }
                else
                {
                    $this->setError( "No se pudo crear la imagen" );
                }

            }
            else
            {
                $this->setError( 'El valor de offset debe ser un número entre -1000 y 1000' );
            }
        }
        else
        {
            $this->setError( 'El valor del divisor debe ser un número entre -1000 y 1000' );
        }
    }

    public function gammaCorrectionImage( $input, $output )
    {
        //validate parameters
        if( is_numeric($input) && $input<=50 && $input>=-50 )
        {
            if( is_numeric($output) && $output<=50 && $output>=-50 )
            {

                //create new and original canvas
                if( $newCanvas = $this->createCanvas() )
                {

                    //preserve transparency
                    if( $this->extension === "png" )
                    {
                        $newCanvas = $this->preserveTransparency( $newCanvas );
                    }

                    imagegammacorrect( $newCanvas , $input,  $output );

                    $this->setError( '' );

                    return $newCanvas;
                }
                else
                {
                    $this->setError( "No se pudo crear la imagen" );
                }

            }
            else
            {
                $this->setError( 'El valor de salida gamma debe ser un número entre -50 y 50' );
            }
        }
        else
        {
            $this->setError( 'El valor entrada gamma debe ser un número entre -50 y 50' );
        }
    }

    public function colorizeImage( $red, $green, $blue, $alpha )
    {
        //validate parameters
        if( is_numeric($red) && $red<=255 && $red>=0 )
        {
            if( is_numeric($green) && $green<=255 && $green>=0 )
            {
                if( is_numeric($blue) && $blue<=255 && $blue>=0 )
                {
                    if( is_numeric($alpha) && $alpha<=127 && $alpha>=0 )
                    {
                        //create new and original canvas
                        if( $newCanvas = $this->createCanvas() )
                        {
                            //preserve transparency
                            if( $this->extension === "png" )
                            {
                                $newCanvas = $this->preserveTransparency( $newCanvas );
                            }

                            imagefilter($newCanvas, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);

                            $this->setError( '' );

                            return $newCanvas;
                        }
                        else
                        {
                            $this->setError( "No se pudo crear la imagen" );
                        }

                    }
                    else
                    {
                        $this->setError( 'El nivel de transparencia debe ser un número entre 0 y 127' );
                    }
                }
                else
                {
                    $this->setError( 'El nivel de azul debe ser un número entre 0 y 255' );
                }
            }
            else
            {
                $this->setError( 'El nivel de verde debe ser un número entre 0 y 255' );
            }
        }
        else
        {
            $this->setError( 'El nivel de rojo debe ser un número entre 0 y 255' );
        }
    }

    public function highlightRedImage()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($rojo<251 && $rojo>240 && $azul+20<$rojo && $verde+20<$rojo && $azul>10 && $verde>10) imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo+5,$verde-10,$azul-10));
                    elseif($rojo>220 && $azul+20<$rojo && $verde+20<$rojo && $rojo<240 && $verde>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo+15,$verde-10,$azul-10));
                    elseif($azul+20<$rojo && $verde+20<$rojo && $rojo<220 && $verde>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo+30,$verde-10,$azul-10));
                }

            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function highlightGreenImage()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($verde<251 && $verde>240 && $azul+20<$verde && $rojo+20<$verde && $azul>10 && $rojo>10) imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-10,$verde+5,$azul-10));
                    elseif($verde>220 && $azul<$verde && $rojo+10<$verde && $verde<240 && $rojo>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-10,$verde+15,$azul-10));
                    elseif($azul<$verde && $rojo+10<$verde && $verde<220 && $rojo>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-10,$verde+30,$azul-10));
                }

            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function highlightBlueImage()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($azul<251 && $azul>240 && $verde+20<$azul && $rojo+20<$azul && $azul>10 && $rojo>10) imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-10,$verde-10,$azul+5));
                    elseif($azul>220 && $verde<$azul && $rojo+10<$azul && $azul<240 && $rojo>10 && $verde>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-10,$verde-10,$azul+15));
                    elseif($verde<$azul && $rojo+10<$azul && $azul<220 && $rojo>10 && $verde>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-10,$verde-10,$azul+30));
                }

            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function attenuateRedImage()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($rojo<251 && $rojo>240 && $azul+20<$rojo && $verde+20<$rojo && $azul>10 && $verde>10) imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-40,$verde,$azul));
                    elseif($rojo>220 && $azul+20<$rojo && $verde+20<$rojo && $rojo<240 && $verde>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-30,$verde,$azul));
                    elseif($azul+20<$rojo && $verde+20<$rojo && $rojo<220 && $rojo>20 && $verde>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo-20,$verde,$azul));
                }

            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function attenuateGreenImage()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($verde<251 && $verde>240 && $azul+20<$verde && $rojo+20<$verde && $azul>10 && $rojo>10) imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo,$verde-40,$azul));
                    elseif($verde>220 && $azul<$verde && $rojo+10<$verde && $verde<240 && $rojo>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo,$verde-30,$azul));
                    elseif($azul<$verde && $rojo+10<$verde && $verde<220 && $verde>20 && $rojo>10 && $azul>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo,$verde-20,$azul));
                }

            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function attenuateBlueImage()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($azul<251 && $azul>240 && $verde+20<$azul && $rojo+20<$azul && $azul>10 && $rojo>10) imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo,$verde,$azul-40));
                    elseif($azul>220 && $verde<$azul && $rojo+10<$azul && $azul<240 && $rojo>10 && $verde>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo,$verde,$azul-30));
                    elseif($verde<$azul && $rojo+10<$azul && $azul<220 && $azul>20 && $rojo>10 && $verde>10)  imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojo,$verde,$azul-20));
                }

            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function superthinpencilEffect()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            imagefilter($newCanvas, IMG_FILTER_EDGEDETECT);

            $blanco = imagecolorallocate($newCanvas, 255,255,255);
            $grisMasMasClaro = imagecolorallocate($newCanvas, 210, 210, 210);
            $grisMasClaro = imagecolorallocate($newCanvas, 175,175,175);
            $grisClaro = imagecolorallocate($newCanvas, 140,140,140);
            $grisOscuro = imagecolorallocate($newCanvas, 105,105,105);
            $grisMasOscuro = imagecolorallocate($newCanvas, 70,70,70);
            $grisMasMasOscuro = imagecolorallocate($newCanvas, 35,35,35);
            $negro = imagecolorallocate($newCanvas, 0,0,0);
            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){
                    $color = ImageColorAt($newCanvas, $x, $y);

                    if(($color&0xFF)>80) imagesetpixel($newCanvas, $x, $y,$blanco);
                    elseif(($color&0xFF)>75) imagesetpixel($newCanvas, $x, $y,$grisMasMasClaro);
                    elseif(($color&0xFF)>70) imagesetpixel($newCanvas, $x, $y,$grisMasClaro);
                    elseif(($color&0xFF)>65) imagesetpixel($newCanvas, $x, $y,$grisClaro);
                    elseif(($color&0xFF)>60) imagesetpixel($newCanvas, $x, $y,$grisOscuro);
                    elseif(($color&0xFF)>60) imagesetpixel($newCanvas, $x, $y,$grisMasOscuro);
                    elseif(($color&0xFF)>55) imagesetpixel($newCanvas, $x, $y,$grisMasMasOscuro);
                    else imagesetpixel($newCanvas, $x, $y,$negro);

                }
            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function thinpencilEffect()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            imagefilter($newCanvas, IMG_FILTER_EDGEDETECT);

            $blanco = imagecolorallocate($newCanvas, 255,255,255);
            $grisMasClaro = imagecolorallocate($newCanvas, 200,200,200);
            $grisClaro = imagecolorallocate($newCanvas, 150,150,150);
            $grisOscuro = imagecolorallocate($newCanvas, 120,120,120);
            $grisMasOscuro = imagecolorallocate($newCanvas, 80,80,80);
            $negro = imagecolorallocate($newCanvas, 0,0,0);
            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){
                    $color = ImageColorAt($newCanvas, $x, $y);

                    if(($color&0xFF)>100) imagesetpixel($newCanvas, $x, $y,$blanco);
                    elseif(($color&0xFF)>95) imagesetpixel($newCanvas, $x, $y,$grisMasClaro);
                    elseif(($color&0xFF)>90) imagesetpixel($newCanvas, $x, $y,$grisClaro);
                    elseif(($color&0xFF)>85) imagesetpixel($newCanvas, $x, $y,$grisOscuro);
                    elseif(($color&0xFF)>80) imagesetpixel($newCanvas, $x, $y,$grisMasOscuro);
                    else imagesetpixel($newCanvas, $x, $y,$negro);
                }
            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function regularpencilEffect()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            imagefilter($newCanvas, IMG_FILTER_EDGEDETECT);

            $blanco = imagecolorallocate($newCanvas, 255,255,255);
            $grisClaro = imagecolorallocate($newCanvas, 230,230,230);
            $grisOscuro = imagecolorallocate($newCanvas, 100,100,100);
            $negro = imagecolorallocate($newCanvas, 0,0,0);
            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){
                    $color = ImageColorAt($newCanvas, $x, $y);

                    if(($color&0xFF)>120) imagesetpixel($newCanvas, $x, $y,$blanco);
                    elseif(($color&0xFF)>110) imagesetpixel($newCanvas, $x, $y,$grisClaro);
                    elseif(($color&0xFF)>100) imagesetpixel($newCanvas, $x, $y,$grisOscuro);
                    else imagesetpixel($newCanvas, $x, $y,$negro);
                }
            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function thickpencilEffect()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            imagefilter($newCanvas, IMG_FILTER_EDGEDETECT);

            $blanco = imagecolorallocate($newCanvas, 255,255,255);
            $negro = imagecolorallocate($newCanvas, 0,0,0);
            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){
                    $color = ImageColorAt($newCanvas, $x, $y);
                    if(($color&0xFF)>120) imagesetpixel($newCanvas, $x, $y,$blanco);
                    else imagesetpixel($newCanvas, $x, $y,$negro);

                }
            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function paintEffect()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){

                    $rojo = (ImageColorAt($newCanvas, $x, $y) >> 16) & 0xFF;
                    $verde = (ImageColorAt($newCanvas, $x, $y) >> 8) & 0xFF;
                    $azul = ImageColorAt($newCanvas, $x, $y) & 0xFF;

                    if($rojo>200) $rojoNuevo = 230;
                    elseif ($rojo>150) $rojoNuevo = 180;
                    elseif ($rojo>100) $rojoNuevo = 130;
                    elseif ($rojo>50) $rojoNuevo = 80;
                    else $rojoNuevo = 30;

                    if($verde>200) $verdeNuevo = 230;
                    elseif ($verde>150) $verdeNuevo = 180;
                    elseif ($verde>100) $verdeNuevo = 130;
                    elseif ($verde>50) $verdeNuevo = 80;
                    else $verdeNuevo = 30;

                    if($azul>200) $azulNuevo = 230;
                    elseif ($azul>150) $azulNuevo = 180;
                    elseif ($azul>100) $azulNuevo = 130;
                    elseif ($azul>50) $azulNuevo = 80;
                    else $azulNuevo = 30;

                    imagesetpixel($newCanvas, $x, $y, imagecolorallocate($newCanvas, $rojoNuevo,$verdeNuevo,$azulNuevo));

                }
            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function CheGuevaraEffect()
    {
        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            imagefilter($newCanvas, IMG_FILTER_EDGEDETECT);
            $rojo = imagecolorallocate($newCanvas, 255,0,0);
            $negro = imagecolorallocate($newCanvas, 0,0,0);
            for($x=0; $x<$this->width; $x++){
                for($y=0; $y<$this->height; $y++){
                    $color = ImageColorAt($newCanvas, $x, $y);

                    if($color>7900000) imagesetpixel($newCanvas, $x, $y,$rojo);
                    else imagesetpixel($newCanvas, $x, $y,$negro);

                }
            }

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    public function overlapEffect( $ImName )
    {
        switch( $ImName )
        {
            case( 'wrinkledPaper' ):
                $overlapImage = self::$WRINKLED_PAPER;
                break;
            case( 'old' ):
                $overlapImage = self::$OLD;
                break;
            case( 'fire' ):
                $overlapImage = self::$FIRE;
                break;
            case( 'drops' ):
                $overlapImage = self::$DROPS;
                break;
            case( 'lights' ):
                $overlapImage = self::$LIGHTS;
                break;
            case( 'colors' ):
                $overlapImage = self::$COLORS;
                break;
            case( 'cool' ):
                $overlapImage = self::$COOL;
                break;
            case( 'horizontal_frame' ):
                $overlapImage = self::$HORIZONTAL_FRAME;
                break;
            case( 'vertical_frame' ):
                $overlapImage = self::$VERTICAL_FRAME;
                break;
            default:
                $overlapImage = self::$WRINKLED_PAPER;
        }

        //create new and original canvas
        if( $newCanvas = $this->createCanvas() )
        {

            //preserve transparency
            if( $this->extension === "png" )
            {
                $newCanvas = $this->preserveTransparency( $newCanvas );
            }

            $newCanvas = $this->mergeImage( $newCanvas, $this->width, $this->height, $overlapImage );

            $this->setError( '' );

            return $newCanvas;
        }
        else
        {
            $this->setError( "No se pudo crear la imagen" );
        }

    }

    private function createCanvas()
    {
        if($this->extension == 'jpeg' || $this->extension == 'jpg')
            $canvas = imagecreatefromjpeg($this->path);
        elseif($this->extension == 'gif')
            $canvas = imagecreatefromgif($this->path);
        elseif($this->extension == 'png')
            $canvas = imagecreatefrompng($this->path);
        elseif($this->extension == 'wbmp')
            $canvas = imagecreatefromwbmp($this->path);
        return $canvas;

    }

    private	function createImage( $canvas, $calidad=100 )
    {
        //Image is created in 'path' directory
        if( $this->extension == 'jpeg' || $this->extension == 'jpg' )
            imagejpeg( $canvas, $this->path, $calidad );

        elseif( $this->extension == 'gif' )
            imagegif( $canvas, $this->path, $calidad );

        elseif( $this->extension == 'png' )
        {
            $this->preserveTransparency();
            imagepng( $canvas, $this->path, $calidad );
        }
        elseif( $this->extension == 'wbmp' )
            imagewbmp( $canvas, $this->path, $calidad );
    }

    private function preserveTransparency( $canvas )
    {
        imagecolortransparent($canvas, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        return $canvas;
    }

    private function mergeImage( $lienzo, $width, $height, $rutaImagen )
    {
        $lienzo_2 = imagecreatefrompng($rutaImagen);

        //Crear lienzo en blanco con proporciones
        $lienzo_2_redim=imagecreatetruecolor($width,$height);

        // preserve transparency
        imagecolortransparent($lienzo_2_redim, imagecolorallocatealpha($lienzo_2_redim, 0, 0, 0, 127));
        imagealphablending($lienzo_2_redim, false);
        imagesavealpha($lienzo_2_redim, true);


        //Copiar $original sobre la imagen que acabamos de crear en blanco ($tmp)
        imagecopyresampled($lienzo_2_redim,$lienzo_2,0,0,0,0,$width, $height,imagesx($lienzo_2),imagesy($lienzo_2));


        imagecopy ( $lienzo , $lienzo_2_redim , 0 , 0 , 0 , 0 , $width , $height );
        return $lienzo;
    }


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
     * Set path
     *
     * @param string $path
     * @return Image
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set size
     *
     * @param integer $size
     * @return Image
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
     * Set name
     *
     * @param string $name
     * @return Image
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return Image
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set extension
     *
     * @param string $extension
     * @return Image
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set error
     *
     * @param string $error
     * @return Image
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
     * Set imageNumber
     *
     * @param integer $number
     * @return Image
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get session id
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set session id
     *
     * @param string $sessionId
     * @return Image
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get imageNumber
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }
    /**
     * Get ruta_imagenes
     *
     * @return string
     */
    public function getRutaImagenes()
    {
        return self::$imagesPath;
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

    public static function getExtensions()
    {
        return array(
            self::JPEG,
            self::JPG,
            self::PNG,
            self::WBMP,
            self::GIF,
        );
    }
}
