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


namespace MSD\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MSD\HomeBundle\Entity\Imagen as Imagen;

class HomeController extends Controller
{
	
	
	

	public function indexAction()
	{
	  	
		if(!isset($_SESSION)) session_start();
		return $this->render('MSDHomeBundle:Home:index.html.twig');
	}
	
	public function verimagenAction()
	{
		$imagen = new Imagen();
		if(!isset($_SESSION)) session_start();
		if(isset($_FILES['userfile'])) {
		if($_FILES['userfile']['size']<$imagen->getSizeMax() && $_FILES['userfile']['size']>=0) 
		{				 
			$imagen->setSize($_FILES['userfile']['size']);
			if(!$errorfiles = $_FILES['userfile']['error']) 
			{
				$imagen->setError('');
				$imagen->setNombre(trim(strtolower($_FILES['userfile']['name'])));
				if(preg_match('/^[a-z0-9áéíóúàèòñç:\.\-_\(\) ]{1,60}\.(jpe?g|png|gif|wbmp)$/', $imagen->getNombre()))
				{
					$imagen->setFormato(substr(strrchr($imagen->getNombre(),'.'),1));
					if($imagen->getFormato() == 'gif' || $imagen->getFormato() == 'jpg' || $imagen->getFormato() == 'jpeg' || $imagen->getFormato() == 'png' || $imagen->getFormato() == 'wbmp')
					{
						if(list($ancho, $alto, $tipo, $atributos) = @getimagesize($_FILES['userfile']['tmp_name'])) 
						{
							if($ancho<10000 && $alto<10000) 
							{		
								if(!is_dir($imagen->getRutaImagenes().session_id())) mkdir($imagen->getRutaImagenes().session_id(),0700);
								$imagen->setRuta(str_replace('.'.$imagen->getFormato(),'0.'.$imagen->getFormato(), $imagen->getRutaImagenes().session_id().'/'.session_id().$imagen->getNombre()));
								if(move_uploaded_file($_FILES['userfile']['tmp_name'], $imagen->getRuta()))
								{
									$imagen->setAncho($ancho);
									$imagen->setAlto($alto);
									$imagen->setNumeroImagen(0);
									$em = $this->getDoctrine()->getEntityManager();
									$em->persist($imagen);
									$em->flush();
									$_SESSION['id'] = $imagen->getId();
									
									return $this->render(
									'MSDHomeBundle:Home:verimagen.html.twig',
									array(
									'imagen'=> array('nombre_imagen'=>$imagen->getNombre(),
													 'ancho'	  =>$imagen->getAncho(),
													 'alto'       =>$imagen->getAlto(),
													 'error'	  =>$imagen->getError()),
									)
									);
								} else $imagen->setError('No se cargó el archivo correctamente');
							} else $imagen->setError('Las dimensiones de la imagen son demasiado grandes');
						} else $imagen->setError('El archivo parece una imagen, pero no lo es o está corrupta');
					} else $imagen->setError('El archivo no tiene formato permitido. Formatos permitidos: gif, jpg, png, wbmp');
				} else $imagen->setError('El nombre de la imagen no se acepta. Se aceptan números, letras, espacios y guiones');
			} 
			else 
			{
				switch ($errorfiles) {
					case UPLOAD_ERR_INI_SIZE:
						$imagen->setError("El archivo excede el tamaño máximo de configuración");
						break;
						case UPLOAD_ERR_FORM_SIZE:
						$imagen->setError("El archivo excede el tamaño máximo establecido");
						break;
						case UPLOAD_ERR_PARTIAL:
						$imagen->setError("El archivo sólo ha sido subido parcialmente");
						break;
						case UPLOAD_ERR_NO_FILE:
						$imagen->setError("No se ha cargado ningún archivo");
						break;
						case UPLOAD_ERR_NO_TMP_DIR:
						$imagen->setError("No existe el directorio temporal");
						break;
						case UPLOAD_ERR_CANT_WRITE:
						$imagen->setError("Falló la escritura del archivo en el disco");
						break;
						case UPLOAD_ERR_EXTENSION:
						$imagen->setError("Error de extensión del archivo");
						break;
						default:
						$imagen->setError("Error desconocido");
						break;
				} 
			}
		} else $imagen->setError('El archivo supera el tamaño máximo permitido de '.$tam_max.' Bytes');
		} else $imagen->setError('No se ha subido la imagen');
		return $this->render(
		'MSDHomeBundle:Home:vererror.html.twig',
		array(
		'imagen'=> array('error'	  =>$imagen->getError())
		)
		);
		
	}	
	
	public function verImagenMuestraAction() 
	{
		if(!isset($_SESSION)) session_start();
		header("Cache-Control: no-cache"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header('Content-type: image/png');
		if(isset($_SESSION['id'])) {
			//call to imagen in database
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			readfile($imagen->getRuta());
		} else {
			readfile('../img/default.png');
		}						 
	}
		
	public function acercadeAction()
	{
		return $this->render('MSDHomeBundle:Acercade:acercade.html.twig');

	}
	
	public function submitAction()
	{
		if(!isset($_SESSION)) session_start();
		if(isset($_SESSION['id'])) {
							
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
		
										
										return $this->render(
										'MSDHomeBundle:Home:submit.html.twig',
										array(
										'imagen'=> array('ruta_imagen'=>$imagen->getRuta(),
														 'ancho'	  =>$imagen->getAncho(),
														 'alto'       =>$imagen->getAlto(),
														 'error'	  =>$imagen->getError()),
										)
										);
									
		return $this->render(
		'MSDHomeBundle:Home:index.html.twig',
		array(
	'error'=>$imagen->getError()
		)
		);
	
		} else {
				return $this->render('MSDHomeBundle:Home:index.html.twig');
			}
		
	}
	
	public function vercambiosAction()
	{		
		//$imagen->setError('');
		if(!isset($_SESSION)) session_start();
		
		if(isset($_SESSION['id'])) {
							
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);


			
			if ($original = $this->crearLienzo($imagen->getRuta(), $imagen->getFormato())) {
		
				
			/***********************************************************
			/***************** CAMBIAR DIMENSIONES *********************
			 * ********************************************************/	
		
			//Comprobar que checkbox cambiar dimensiones está activado
			if(isset($_POST['cambiar_dim']) && $_POST['cambiar_dim']=='check') {						
				//Comprobar que los datos recibidos son correctos
				if(isset($_POST['dimensionesX']) && isset($_POST['dimensionesY'])) {
					$dimx=$_POST['dimensionesX'];
					$dimy=$_POST['dimensionesY'];
					if($dimx && $dimy) {
						if(is_numeric($dimx) && is_numeric($dimy)) {
							if(($dimx<=$imagen->getAncho()+1000) && ($dimy<=$imagen->getAlto()+1000) && $dimx>=0 && $dimy>=0) {
								if(($dimx<=6000) && ($dimy<=6000)) {
			
									//cambiar las dimensiones de la imagen
									
									//Crear lienzo en blanco con proporciones
									$lienzo=imagecreatetruecolor($dimx,$dimy);

									// preserve transparency
									if($imagen->getFormato() == "gif" or $imagen->getFormato() == "png"){
										imagecolortransparent($lienzo, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
									    imagealphablending($lienzo, false);
									    imagesavealpha($lienzo, true);
									}
									
									//Copiar $original sobre la imagen que acabamos de crear en blanco ($tmp)
									imagecopyresampled($lienzo,$original,0,0,0,0,$dimx, $dimy,$imagen->getAncho(),$imagen->getAlto());;
									
									$imagen->setError('');
									$imagen->setAlto($dimy);
									$imagen->setAncho($dimx);
									
								} else $imagen->setError('Las dimensiones introducidas son demasiado grandes. Máximo 6000 x 6000');
							} else $imagen->setError('Las dimensiones introducidas son demasiado grandes. Máximo escalas de +1000');
						} else $imagen->setError('Los datos introducidos no son enteros');
					} else $imagen->setError('Los datos introducidos no pueden ser nulos');
				} else $imagen->setError('No se han cargado datos');
			}
			
			
			
			/***********************************************************
			/***************** RECORTAR IMAGEN ****************************
			 * ********************************************************/
			
			//Recortar imagen
			
			if(isset($_POST['modif_recortar']) && ($_POST['modif_recortar']=='check')) {		
				if(isset($_POST['recortar_izq']) && isset($_POST['recortar_der'])  && isset($_POST['recortar_arr'])  && isset($_POST['recortar_aba'])) {
					if(is_numeric($_POST['recortar_izq']) && is_numeric($_POST['recortar_der']) && is_numeric($_POST['recortar_arr']) && is_numeric($_POST['recortar_aba'])) { 
						if($_POST['recortar_izq']>=0 && $_POST['recortar_izq']+$_POST['recortar_der']<$imagen->getAncho()
						&& $_POST['recortar_der']>=0  
						&& $_POST['recortar_arr']>=0 && $_POST['recortar_arr']+$_POST['recortar_aba']<$imagen->getAlto() 
						&& $_POST['recortar_aba']>=0) 	
						{	
						//recortar la imagen
									
						//Crear lienzo en blanco con proporciones
						$lienzo=imagecreatetruecolor($imagen->getAncho()-$_POST['recortar_izq']-$_POST['recortar_der'],$imagen->getAlto()-$_POST['recortar_arr']-$_POST['recortar_aba']);
	
						// preserve transparency
						if($imagen->getFormato() == "gif" or $imagen->getFormato() == "png"){
							imagecolortransparent($lienzo, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
						    imagealphablending($lienzo, false);
						    imagesavealpha($lienzo, true);
						}
						
						//Copiar $original sobre la imagen que acabamos de crear en blanco ($tmp)
						imagecopyresampled($lienzo,$original,0,0,$_POST['recortar_izq'],$_POST['recortar_arr'],$imagen->getAncho()-$_POST['recortar_izq']-$_POST['recortar_der'],$imagen->getAlto()-$_POST['recortar_aba']-$_POST['recortar_arr'],$imagen->getAncho()-$_POST['recortar_izq']-$_POST['recortar_der'],$imagen->getAlto()-$_POST['recortar_aba']-$_POST['recortar_arr']);
					
						$imagen->setError('');
						$imagen->setAlto(floor($imagen->getAlto()-$_POST['recortar_arr']-$_POST['recortar_aba']));
						$imagen->setAncho(floor($imagen->getAncho()-$_POST['recortar_der']-$_POST['recortar_izq']));
						
						} else $imagen->setError('Los valores de recorte no son correctos');
					} else $imagen->setError('Los valores de recorte deben ser numéricos');
				} else $imagen->setError('No se han cargado datos.');
			}
				
			if(!isset($lienzo))
			$lienzo=$original;
			
			/***********************************************************
			/***************** ROTAR IMAGEN ****************************
			 * ********************************************************/
			
			//Rotar imagen
			if(isset($_POST['rotar']) && ($_POST['rotar']=='check')) {		
				if(isset($_POST['rotacion']) && is_numeric($_POST['rotacion']) && $_POST['rotacion']<=360 && $_POST['rotacion']>=0 ) {	
					//cambiar las dimensiones de la imagen
									
					$x = abs(floor(($imagen->getAlto() * cos(deg2rad(90 - $_POST['rotacion']))) + abs($imagen->getAncho() * cos(deg2rad($_POST['rotacion']))))-2);
					$y = abs(floor(($imagen->getAlto() * cos(deg2rad($_POST['rotacion']))) + abs($imagen->getAncho() * cos(deg2rad(90 - $_POST['rotacion']))))-2);
					$imagen->setAlto($y);
					$imagen->setAncho($x);
					if($lienzo = imagerotate ($lienzo, $_POST['rotacion'], 0)){
							
					}else $imagen->setError('La rotación no fue realizada');
				} else $imagen->setError('El ángulo debe ser un número entre 0 y 360');
			}
				
			
			/***********************************************************
			/***************** CAMBIAR BRILLO Y CONTRASTE **************
			 * ********************************************************/	
			
			//Cambiar brillo
			if(isset($_POST['modif_brillo']) && ($_POST['modif_brillo']=='check')) {		
				if(isset($_POST['brillo']) && is_numeric($_POST['brillo']) && $_POST['brillo']<256 && $_POST['brillo']>-256) {
				imagefilter($lienzo, IMG_FILTER_BRIGHTNESS, $_POST['brillo']);
				} else $imagen->setError('El brillo debe ser un número entre -255 y 255');
			}
			
			//Cambiar contraste
			if(isset($_POST['modif_contraste']) && ($_POST['modif_contraste']=='check')) {		
				if(isset($_POST['contraste']) && is_numeric($_POST['contraste']) && $_POST['contraste']<=1000 && $_POST['contraste']>=-1000 ) {
				imagefilter($lienzo, IMG_FILTER_CONTRAST, $_POST['contraste']);
				} else $imagen->setError('El contraste debe ser un número entre -1000 y 1000');
			}
			
			
			/***********************************************************
			/***************** APLICAR FILTROS *********************
			 * ********************************************************/
			
			//Filtro grises
			if(isset($_POST['convertir_grises']) && ($_POST['convertir_grises']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_GRAYSCALE);
			}
			
			//Filtro negativo
			if(isset($_POST['convertir_negativo']) && ($_POST['convertir_negativo']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_NEGATE);
			}
			
			//Filtro resaltar bordes
			if(isset($_POST['resaltar_bordes']) && ($_POST['resaltar_bordes']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_EDGEDETECT);
			}
		
			//Filtro resaltar relieve
			if(isset($_POST['resaltar_relieve']) && ($_POST['resaltar_relieve']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_EMBOSS);
			}		
			
			//Filtro eliminación media (efecto superficial)
			if(isset($_POST['elimin_media']) && ($_POST['elimin_media']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_MEAN_REMOVAL);
			}	
			
			//Filtro imagen borrosa
			if(isset($_POST['convertir_borroso']) && ($_POST['convertir_borroso']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_SELECTIVE_BLUR);
			}	
			
			//Filtro imagen borrosa (método Gaussiano)
			if(isset($_POST['convertir_borroso_Gauss']) && ($_POST['convertir_borroso_Gauss']=='check')) {		
				imagefilter($lienzo, IMG_FILTER_GAUSSIAN_BLUR);
			}				
			
			//Filtro suavizado
			if(isset($_POST['convertir_suavizado']) && ($_POST['convertir_suavizado']=='check')) {		
				if(isset($_POST['suavizado']) && is_numeric($_POST['suavizado']) && $_POST['suavizado']<=5000 && $_POST['suavizado']>=-5000 ) {
				imagefilter($lienzo, IMG_FILTER_SMOOTH, $_POST['suavizado']);
				} else $imagen->setError('El suavizado debe ser un número entre -5000 y 5000');
			}
			
			//Filtro pixelación
			if(isset($_POST['convertir_pixelacion']) && ($_POST['convertir_pixelacion']=='check')) {		
				if(isset($_POST['pixelar']) && is_numeric($_POST['pixelar']) && $_POST['pixelar']<=1000000 && $_POST['pixelar']>=0 ) {
					
					$maxX = imagesx($lienzo);
				    $maxY = imagesy($lienzo);
				    $rad=floor($_POST['pixelar']/2);
				    for($x=$rad;$x<$maxX;$x+=$_POST['pixelar'])
				        for($y=$rad;$y<$maxY;$y+=$_POST['pixelar']){
				            $color = imagecolorat($lienzo, $x, $y);
				            imagefilledrectangle ($lienzo, $x-$rad, $y-$rad, $x+$_POST['pixelar']-1, $y+$_POST['pixelar']-1,$color);
				        }
				} else $imagen->setError('El tamaño de bloque debe ser un número entre 0 y 1000000');
			}
			
			//Convolución
			if(isset($_POST['convertir_convolucion']) && ($_POST['convertir_convolucion']=='check')) {		
				if(isset($_POST['convolucion_divisor']) && is_numeric($_POST['convolucion_divisor']) && $_POST['convolucion_divisor']<=1000 && $_POST['convolucion_divisor']>=-1000 ) {
					if(isset($_POST['convolucion_offset']) && is_numeric($_POST['convolucion_offset']) && $_POST['convolucion_offset']<=1000 && $_POST['convolucion_offset']>=-1000 ) {	
						$matriz= array(array(3), array(3), array(3));
						for($i=0;$i<=8;$i++) {
							if(!(isset($_POST['convolucion_matriz_'.$i]) && is_numeric($_POST['convolucion_matriz_'.$i]) && $_POST['convolucion_matriz_'.$i]<=255 && $_POST['convolucion_matriz_'.$i]>=-255 )) {
								$imagen->setError('Los valores de la matriz deben ser números entre -255 y +255');
								break;
							} else { 
								if($i<3) $matriz[0][$i] = $_POST['convolucion_matriz_'.$i];
								elseif($i<6) $matriz[1][$i-3] = $_POST['convolucion_matriz_'.$i];
								elseif($i<9) $matriz[2][$i-6] = $_POST['convolucion_matriz_'.$i];
							}
						}
						if($imagen->getError()=='') {
							imageconvolution($lienzo , $matriz,  $_POST['convolucion_divisor'],  $_POST['convolucion_offset'] );
						}
					} else $imagen->setError('El valor de offset debe ser un número entre -1000 y 1000');
				} else $imagen->setError('El valor del divisor debe ser un número entre -1000 y 1000');
			} 
			
			//Corrección gamma
			if(isset($_POST['convertir_gamma']) && ($_POST['convertir_gamma']=='check')) {
				if(isset($_POST['entrada_gamma']) && is_numeric($_POST['entrada_gamma']) && $_POST['entrada_gamma']<=50 && $_POST['entrada_gamma']>=-50 ) {
					if(isset($_POST['salida_gamma']) && is_numeric($_POST['salida_gamma']) && $_POST['salida_gamma']<=50 && $_POST['salida_gamma']>=-50 ) {
						// preserve transparency
						if($imagen->getFormato() == "gif" or $imagen->getFormato() == "png"){
							imagecolortransparent($lienzo, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
						    imagealphablending($lienzo, false);
						    imagesavealpha($lienzo, true);
						}
						imagegammacorrect($lienzo , $_POST['entrada_gamma'], $_POST['salida_gamma']);
					} else $imagen->setError('El valor de salida gamma debe ser un número entre -50 y 50');
				} else $imagen->setError('El valor entrada gamma debe ser un número entre -50 y 50');
			}
			
			/***********************************************************
			/***************** EFECTOS ********************************
			 * ********************************************************/
			
							
			//Filtro colorear
			if(isset($_POST['convertir_colorear']) && ($_POST['convertir_colorear']=='check')) {		
				if(isset($_POST['colorear_r']) && is_numeric($_POST['colorear_r']) && $_POST['colorear_r']<=255 && $_POST['colorear_r']>=0 ) {
					if(isset($_POST['colorear_g']) && is_numeric($_POST['colorear_g']) && $_POST['colorear_g']<=255 && $_POST['colorear_g']>=0 ) {
						if(isset($_POST['colorear_b']) && is_numeric($_POST['colorear_b']) && $_POST['colorear_b']<=255 && $_POST['colorear_b']>=0 ) {
							if(isset($_POST['alfa']) && is_numeric($_POST['alfa']) && $_POST['alfa']<=127 && $_POST['alfa']>=0 ) {
							imagefilter($lienzo, IMG_FILTER_COLORIZE, $_POST['colorear_r'], $_POST['colorear_g'], $_POST['colorear_b']);
							} else $imagen->setError('El nivel de transparencia debe ser un número entre 0 y 127');
						} else $imagen->setError('El nivel de azul debe ser un número entre 0 y 255');
					} else $imagen->setError('El nivel de verde debe ser un número entre 0 y 255');
				} else $imagen->setError('El nivel de rojo debe ser un número entre 0 y 255');
			}
			
			//Resaltar colores
			if(isset($_POST['convertir_resaltar_colores']) && ($_POST['convertir_resaltar_colores']=='check')) {		
				
				if(isset($_POST['resaltar_colores_r'])  && ($_POST['resaltar_colores_r']=='check')) {
					
					for($x=0; $x<$imagen->getAncho(); $x++){
						for($y=0; $y<$imagen->getAlto(); $y++){
						
						$rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;
						
						if($rojo<251 && $rojo>240 && $azul+20<$rojo && $verde+20<$rojo && $azul>10 && $verde>10) imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo+5,$verde-10,$azul-10));  			            
			            elseif($rojo>220 && $azul+20<$rojo && $verde+20<$rojo && $rojo<240 && $verde>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo+15,$verde-10,$azul-10));  
			            elseif($azul+20<$rojo && $verde+20<$rojo && $rojo<220 && $verde>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo+30,$verde-10,$azul-10));  
						}
						
					}	
					
				} elseif(isset($_POST['resaltar_colores_g'])  && ($_POST['resaltar_colores_g']=='check')) {
					for($x=0; $x<$imagen->getAncho(); $x++){
						for($y=0; $y<$imagen->getAlto(); $y++){
				
						$rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;
				
						if($verde<251 && $verde>240 && $azul+20<$verde && $rojo+20<$verde && $azul>10 && $rojo>10) imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-10,$verde+5,$azul-10));  			            
			            elseif($verde>220 && $azul<$verde && $rojo+10<$verde && $verde<240 && $rojo>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-10,$verde+15,$azul-10));  
			            elseif($azul<$verde && $rojo+10<$verde && $verde<220 && $rojo>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-10,$verde+30,$azul-10));  
						}
						
					}
				} elseif(isset($_POST['resaltar_colores_b'])  && ($_POST['resaltar_colores_b']=='check')) {
					for($x=0; $x<$imagen->getAncho(); $x++){
						for($y=0; $y<$imagen->getAlto(); $y++){

						$rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;

						if($azul<251 && $azul>240 && $verde+20<$azul && $rojo+20<$azul && $azul>10 && $rojo>10) imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-10,$verde-10,$azul+5));  			            
			            elseif($azul>220 && $verde<$azul && $rojo+10<$azul && $azul<240 && $rojo>10 && $verde>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-10,$verde-10,$azul+15));  
			            elseif($verde<$azul && $rojo+10<$azul && $azul<220 && $rojo>10 && $verde>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-10,$verde-10,$azul+30));  
						}
						
					}
				}
													
			}
		
			//Atenuar colores
			if(isset($_POST['convertir_atenuar_colores']) && ($_POST['convertir_atenuar_colores']=='check')) {		
				
				if(isset($_POST['atenuar_colores_r'])  && ($_POST['atenuar_colores_r']=='check')) {
					
					for($x=0; $x<$imagen->getAncho(); $x++){
						for($y=0; $y<$imagen->getAlto(); $y++){
						
						$rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;
						
						if($rojo<251 && $rojo>240 && $azul+20<$rojo && $verde+20<$rojo && $azul>10 && $verde>10) imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-40,$verde,$azul));  			            
			            elseif($rojo>220 && $azul+20<$rojo && $verde+20<$rojo && $rojo<240 && $verde>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-30,$verde,$azul));  
			            elseif($azul+20<$rojo && $verde+20<$rojo && $rojo<220 && $rojo>20 && $verde>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo-20,$verde,$azul));  
						}
						
					}	
					
				} elseif(isset($_POST['atenuar_colores_g'])  && ($_POST['atenuar_colores_g']=='check')) {
					for($x=0; $x<$imagen->getAncho(); $x++){
						for($y=0; $y<$imagen->getAlto(); $y++){
				
						$rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;
				
						if($verde<251 && $verde>240 && $azul+20<$verde && $rojo+20<$verde && $azul>10 && $rojo>10) imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo,$verde-40,$azul));  			            
			            elseif($verde>220 && $azul<$verde && $rojo+10<$verde && $verde<240 && $rojo>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo,$verde-30,$azul));  
			            elseif($azul<$verde && $rojo+10<$verde && $verde<220 && $verde>20 && $rojo>10 && $azul>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo,$verde-20,$azul));  
						}
						
					}
				} elseif(isset($_POST['atenuar_colores_b'])  && ($_POST['atenuar_colores_b']=='check')) {
					for($x=0; $x<$imagen->getAncho(); $x++){
						for($y=0; $y<$imagen->getAlto(); $y++){

						$rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;

						if($azul<251 && $azul>240 && $verde+20<$azul && $rojo+20<$azul && $azul>10 && $rojo>10) imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo,$verde,$azul-40));  			            
			            elseif($azul>220 && $verde<$azul && $rojo+10<$azul && $azul<240 && $rojo>10 && $verde>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo,$verde,$azul-30));  
			            elseif($verde<$azul && $rojo+10<$azul && $azul<220 && $azul>20 && $rojo>10 && $verde>10)  imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojo,$verde,$azul-20));  
						}
						
					}
				}
													
			}
			
			//Efecto dibujo a lápiz super fino
			if(isset($_POST['convertir_lapiz_super_fino']) && ($_POST['convertir_lapiz_super_fino']=='check')) {
						
				imagefilter($lienzo, IMG_FILTER_EDGEDETECT);
				
				$blanco = imagecolorallocate($lienzo, 255,255,255);
				$grisMasMasClaro = imagecolorallocate($lienzo, 210, 210, 210);
				$grisMasClaro = imagecolorallocate($lienzo, 175,175,175);
				$grisClaro = imagecolorallocate($lienzo, 140,140,140);
				$grisOscuro = imagecolorallocate($lienzo, 105,105,105);
				$grisMasOscuro = imagecolorallocate($lienzo, 70,70,70);
				$grisMasMasOscuro = imagecolorallocate($lienzo, 35,35,35);
				$negro = imagecolorallocate($lienzo, 0,0,0);
				for($x=0; $x<$imagen->getAncho(); $x++){
			        for($y=0; $y<$imagen->getAlto(); $y++){
			            $color = ImageColorAt($lienzo, $x, $y);
			            
						if(($color&0xFF)>80) imagesetpixel($lienzo, $x, $y,$blanco);  
						elseif(($color&0xFF)>75) imagesetpixel($lienzo, $x, $y,$grisMasMasClaro);
			            elseif(($color&0xFF)>70) imagesetpixel($lienzo, $x, $y,$grisMasClaro);
			            elseif(($color&0xFF)>65) imagesetpixel($lienzo, $x, $y,$grisClaro);
			            elseif(($color&0xFF)>60) imagesetpixel($lienzo, $x, $y,$grisOscuro);
			            elseif(($color&0xFF)>60) imagesetpixel($lienzo, $x, $y,$grisMasOscuro);	
			            elseif(($color&0xFF)>55) imagesetpixel($lienzo, $x, $y,$grisMasMasOscuro);					              
			            else imagesetpixel($lienzo, $x, $y,$negro);  	
	
					}
			    }
			}
			
			//Efecto dibujo a lápiz fino
			if(isset($_POST['convertir_lapiz_fino']) && ($_POST['convertir_lapiz_fino']=='check')) {
						
				imagefilter($lienzo, IMG_FILTER_EDGEDETECT);
				
				$blanco = imagecolorallocate($lienzo, 255,255,255);
				$grisMasClaro = imagecolorallocate($lienzo, 200,200,200);
				$grisClaro = imagecolorallocate($lienzo, 150,150,150);
				$grisOscuro = imagecolorallocate($lienzo, 120,120,120);
				$grisMasOscuro = imagecolorallocate($lienzo, 80,80,80);
				$negro = imagecolorallocate($lienzo, 0,0,0);
				for($x=0; $x<$imagen->getAncho(); $x++){
			        for($y=0; $y<$imagen->getAlto(); $y++){
			            $color = ImageColorAt($lienzo, $x, $y);
			            
			            if(($color&0xFF)>100) imagesetpixel($lienzo, $x, $y,$blanco);  
			            elseif(($color&0xFF)>95) imagesetpixel($lienzo, $x, $y,$grisMasClaro);
			            elseif(($color&0xFF)>90) imagesetpixel($lienzo, $x, $y,$grisClaro);
			            elseif(($color&0xFF)>85) imagesetpixel($lienzo, $x, $y,$grisOscuro);
			            elseif(($color&0xFF)>80) imagesetpixel($lienzo, $x, $y,$grisMasOscuro);					              
			            else imagesetpixel($lienzo, $x, $y,$negro);  	
					}
			    }
			}
			
			//Efecto dibujo a lápiz normal
			if(isset($_POST['convertir_lapiz_normal']) && ($_POST['convertir_lapiz_normal']=='check')) {
						
				imagefilter($lienzo, IMG_FILTER_EDGEDETECT);
				$blanco = imagecolorallocate($lienzo, 255,255,255);
				$grisClaro = imagecolorallocate($lienzo, 230,230,230);
				$grisOscuro = imagecolorallocate($lienzo, 100,100,100);
				$negro = imagecolorallocate($lienzo, 0,0,0);
				for($x=0; $x<$imagen->getAncho(); $x++){
			        for($y=0; $y<$imagen->getAlto(); $y++){
			            $color = ImageColorAt($lienzo, $x, $y);
			            
			            if(($color&0xFF)>120) imagesetpixel($lienzo, $x, $y,$blanco);  
			            elseif(($color&0xFF)>110) imagesetpixel($lienzo, $x, $y,$grisClaro);
			            elseif(($color&0xFF)>100) imagesetpixel($lienzo, $x, $y,$grisOscuro);					              
			            else imagesetpixel($lienzo, $x, $y,$negro);  	
					}
			    }					
			}
			
			//Efecto dibujo a lápiz grueso
			if(isset($_POST['convertir_lapiz_grueso']) && ($_POST['convertir_lapiz_grueso']=='check')) {
				
				imagefilter($lienzo, IMG_FILTER_EDGEDETECT);
				$blanco = imagecolorallocate($lienzo, 255,255,255);
				$negro = imagecolorallocate($lienzo, 0,0,0);
				for($x=0; $x<$imagen->getAncho(); $x++){
			        for($y=0; $y<$imagen->getAlto(); $y++){
			            $color = ImageColorAt($lienzo, $x, $y);
			           // echo ($color&0xFF).'</br>';
			            if(($color&0xFF)>120) imagesetpixel($lienzo, $x, $y,$blanco);  
			            else imagesetpixel($lienzo, $x, $y,$negro);  
		
			        }
			    }
			}
			
			//Efecto pintura
			if(isset($_POST['convertir_pintura']) && ($_POST['convertir_pintura']=='check')) {
			
				for($x=0; $x<$imagen->getAncho(); $x++){
			        for($y=0; $y<$imagen->getAlto(); $y++){
			            
			            $rojo = (ImageColorAt($lienzo, $x, $y) >> 16) & 0xFF;
			            $verde = (ImageColorAt($lienzo, $x, $y) >> 8) & 0xFF;
			            $azul = ImageColorAt($lienzo, $x, $y) & 0xFF;
						
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
						
						imagesetpixel($lienzo, $x, $y, imagecolorallocate($lienzo, $rojoNuevo,$verdeNuevo,$azulNuevo));
						
					}
				}					
			}

			
			//Efecto che
			if(isset($_POST['convertir_che']) && ($_POST['convertir_che']=='check')) {
				
						imagefilter($lienzo, IMG_FILTER_EDGEDETECT);
						$rojo = imagecolorallocate($lienzo, 255,0,0);
						$negro = imagecolorallocate($lienzo, 0,0,0);
						for($x=0; $x<$imagen->getAncho(); $x++){
					        for($y=0; $y<$imagen->getAlto(); $y++){
					            $color = ImageColorAt($lienzo, $x, $y);
					            
					            if($color>7900000) imagesetpixel($lienzo, $x, $y,$rojo);  
					            else imagesetpixel($lienzo, $x, $y,$negro);  
				
					        }
					    }
		
			}
			
			//Efecto papel arrugado
			if(isset($_POST['convertir_papel_arr']) && ($_POST['convertir_papel_arr']=='check')) {			
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/papel_arr2.png' );			
			}

			//Efecto antiguo
			if(isset($_POST['convertir_antiguo']) && ($_POST['convertir_antiguo']=='check')) {				
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/antiguo.png' );
			}
			
			//Efecto fuego			
			if(isset($_POST['convertir_fuego']) && ($_POST['convertir_fuego']=='check')) {			
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/fuego.png' );
			}
			
			//Efecto luces
			if(isset($_POST['convertir_luces']) && ($_POST['convertir_luces']=='check')) {				
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/luces.png' );
			}

			//Efecto gotas
			if(isset($_POST['convertir_gotas']) && ($_POST['convertir_gotas']=='check')) {				
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/gotas.png' );
			}

			//Efecto colores
			if(isset($_POST['convertir_colores']) && ($_POST['convertir_colores']=='check')) {				
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/colores.png' );
			}
			
			//Efecto molón
			if(isset($_POST['convertir_molon']) && ($_POST['convertir_molon']=='check')) {				
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), '../img/molon.png' );
			}
			
			
			//Enmarcar			
			if((isset($_POST['convertir_marco_horizontal']) && ($_POST['convertir_marco_horizontal']=='check'))  || (isset($_POST['convertir_marco_vertical']) && ($_POST['convertir_marco_vertical']=='check'))) {			
				$ruta = (isset($_POST['convertir_marco_horizontal']) && ($_POST['convertir_marco_horizontal']=='check')) ? '../img/marco_horizontal.png' : '../img/marco_vertical.png';		
				$lienzo = $this->mergeImage( $lienzo, $imagen->getAncho(), $imagen->getAlto(), $ruta );
			}
			
			
		} else $imagen->setError('No se pudo crear la nueva imagen');
			/***********************************************************
			 ***************** CREAR NUEVA IMAGEN **********************
			 * *************** Y BORRAR ANTIGUAS ***********************
			 * ********************************************************/
			
			if($imagen->getError()==='') {
				$imagen->setNumeroImagen($imagen->getNumeroImagen() + 1);			
				if($imagen->getNumeroImagen() < 11) {
					$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-1);
				} elseif ($imagen->getNumeroImagen() >=11 && $imagen->getNumeroImagen() <101) {
					$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-2);
				} elseif ($imagen->getNumeroImagen() >=101 && $imagen->getNumeroImagen() <1001) {
					$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-3);
				} elseif ($imagen->getNumeroImagen() >=1001 && $$imagen->getNumeroImagen() <5001) {
					$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-4);
				} else {
					$imagen->setError('Has sobrepasado el límite. Recarga la página');
					$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-4);
					for($i=0;$i<=5;$i++) {
						if(file_exists(str_replace($imagen->getNumeroImagen().'.'.$imagen->getFormato(),($imagen->getNumeroImagen()-$i).'.'.$imagen->getFormato(),$imagen->getRuta())))
							unlink(str_replace($imagen->getNumeroImagen().'.'.$imagen->getFormato(),($imagen->getNumeroImagen()-$i).'.'.$imagen->getFormato(),$imagen->getRuta()));
						}
					exit();
				}
				
				
				$imagen->setRuta(str_replace($rempl,$imagen->getNumeroImagen().'.'.$imagen->getFormato(),$imagen->getRuta()));
				$this->crearImagen($lienzo, $imagen->getRuta(), $imagen->getFormato());
							
				//Borramos imagen anterior a la anterior
				if($imagen->getNumeroImagen() >= 5) {
					if(file_exists(str_replace($imagen->getNumeroImagen().'.'.$imagen->getFormato(),($imagen->getNumeroImagen()-5).'.'.$imagen->getFormato(),$imagen->getRuta())))
						unlink(str_replace($imagen->getNumeroImagen().'.'.$imagen->getFormato(),($imagen->getNumeroImagen()-5).'.'.$imagen->getFormato(),$imagen->getRuta()));		
				}
				
				$em->flush();
			}	
				imagedestroy($original);
				
				return $this->render(
				'MSDHomeBundle:Home:vercambios.html.twig',
				array(
				'imagen' => array('dimx' => $imagen->getAncho(),
								  'dimy' => $imagen->getAlto(),
								  'error' => $imagen->getError()
				)
				)
				);
			} else {
				return $this->render(
				'MSDHomeBundle:Home:vercambios.html.twig',
				array(
				'imagen' => array('dimx' => 0,
								  'dimy' => 0,
								  'error' => 'tu sesión ha caducado. Vuelve a probar'
				)
				)
				);
			}
		}

public function guardarAction()
{
	if(!isset($_SESSION)) session_start();
		
	if(isset($_SESSION['id'])) {	

		$em = $this->getDoctrine()->getEntityManager();
		$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
		// open the file in a binary mode
		$fp = fopen($imagen->getRuta(), 'rb');
		header("Content-Type: image/".$imagen->getFormato());
		//header("Content-Length: ".$imagen->getSize());
		header('Content-Disposition: attachment; filename="imagen.'.$imagen->getFormato().'"');

		// dump the picture and stop the script
		fpassthru($fp);
		exit;
	}
}

public function borrarAction()
	{		
	if(!isset($_SESSION)) session_start();
		
	if(isset($_SESSION['id'])) {	

		$em = $this->getDoctrine()->getEntityManager();
		$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

		
		if($imagen->getNumeroImagen() < 10) 
		$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-1);
		elseif($imagen->getNumeroImagen() >=10 && $imagen->getNumeroImagen() < 100) 
		$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-2);
		elseif($imagen->getNumeroImagen() >=100 && $imagen->getNumeroImagen() < 1000) 
		$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-3);
		else $rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-4);
		
		for($i=-5; $i<=5; $i++) {
			if (($imagen->getNumeroImagen()+$i)>=0) {

				$imagen->setRuta(str_replace($rempl,($imagen->getNumeroImagen()+$i).'.'.$imagen->getFormato(),$imagen->getRuta()));
				
				//$a = strchr($rempl,'.'); //.jpg
				//$b = str_replace($a,'',$rempl); //3
				//$c = str_replace($b, ($imagen->getNumeroImagen()+$i), $rempl);
				
				$rempl = str_replace(str_replace(strchr($rempl,'.'),'',$rempl), ($imagen->getNumeroImagen()+$i), $rempl);
				
				if(file_exists($imagen->getRuta()))
					unlink($imagen->getRuta());
			}
		}
		$em->remove($imagen);
		$em->flush();

		session_destroy();
	
	} 
	return $this->render(
	'MSDHomeBundle:Home:index.html.twig',
	array(
	'error' => '')
	);
	}

public function atrasAction()
	{		
	if(!isset($_SESSION)) session_start();
		
	if(isset($_SESSION['id'])) {	

		$em = $this->getDoctrine()->getEntityManager();
		$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
	
		if($imagen->getNumeroImagen() > 0) {
			if($imagen->getNumeroImagen() < 10) 
			$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-1);
			elseif($imagen->getNumeroImagen() >=10 && $imagen->getNumeroImagen() < 100) 
			$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-2);
			elseif($imagen->getNumeroImagen() >=100 && $imagen->getNumeroImagen() < 1000) 
			$rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-3);
			else $rempl = substr($imagen->getRuta(),strrpos($imagen->getRuta(),'.')-4);
			if(file_exists(str_replace($rempl,($imagen->getNumeroImagen()-1).'.'.$imagen->getFormato(),$imagen->getRuta()))) {
				$ruta_orig = $imagen->getRuta();
				$imagen->setRuta(str_replace($rempl,($imagen->getNumeroImagen()-1).'.'.$imagen->getFormato(),$imagen->getRuta()));
				$imagen->setNumeroImagen(($imagen->getNumeroImagen())-1);

				if(list($ancho, $alto) = @getimagesize($imagen->getRuta())) {

					$imagen->setAncho($ancho);
					$imagen->setAlto($alto);
					
					//Borramos imagen anterior
					if($imagen->getNumeroImagen() >= 0) {
						if(file_exists($ruta_orig))
							unlink($ruta_orig);		
					}
				}  				
			} 
		
		$em->flush();
		
		} 
		
		return $this->render(
		'MSDHomeBundle:Home:vercambios.html.twig',
		array(
		'imagen' => array('dimx' => $imagen->getAncho(),
						  'dimy' => $imagen->getAlto(),
						  'error' => $imagen->getError())
		)
		);	 
	}
	}

	/*******************************************/
	/********* Crear lienzo a partir de ********/
	/********* imagen con formato conocido******/
	/*******************************************/
	
	function crearLienzo($ruta_imagen, $formato)
	{
				
		if($formato == 'jpeg' || $formato == 'jpg')
		$lienzo = imagecreatefromjpeg($ruta_imagen);
		elseif($formato == 'gif')
		$lienzo = imagecreatefromgif($ruta_imagen);
		elseif($formato == 'png') 
		$lienzo = imagecreatefrompng($ruta_imagen);
		elseif($formato == 'wbmp')
		$lienzo = imagecreatefromwbmp($ruta_imagen);
		return $lienzo;
		
	}
	
	
	/*******************************************/
	/********* Crear imagen a partir de ********/
	/********* lienzo **************************/
	/*******************************************/
	function crearImagen($lienzo, $nueva_ruta, $formato, $calidad=100)
	{
			
		//Se crea la imagen final en el directorio indicado
		if($formato == 'jpeg' || $formato == 'jpg')
		imagejpeg($lienzo,$nueva_ruta,$calidad);
		elseif($formato == 'gif')
		imagegif($lienzo,$nueva_ruta,$calidad);
		elseif($formato == 'png') {
			// Desactivar la mezcla alfa y establecer la bandera alfa
			imagealphablending($lienzo, false);
			imagesavealpha($lienzo, true);
			imagepng($lienzo,$nueva_ruta);
		}
		elseif($formato == 'wbmp')
		imagewbmp($lienzo,$nueva_ruta,$calidad);

	}
	
	function mergeImage( $lienzo, $ancho, $alto, $rutaImagen ) 
	{
		$lienzo_2 = $this->crearLienzo($rutaImagen, 'png');	
		
		//Crear lienzo en blanco con proporciones
		$lienzo_2_redim=imagecreatetruecolor($ancho,$alto);

		// preserve transparency
		imagecolortransparent($lienzo_2_redim, imagecolorallocatealpha($lienzo_2_redim, 0, 0, 0, 127));
	    imagealphablending($lienzo_2_redim, false);
	    imagesavealpha($lienzo_2_redim, true);
	
		
		//Copiar $original sobre la imagen que acabamos de crear en blanco ($tmp)
		imagecopyresampled($lienzo_2_redim,$lienzo_2,0,0,0,0,$ancho, $alto,imagesx($lienzo_2),imagesy($lienzo_2));
		
		
		imagecopy ( $lienzo , $lienzo_2_redim , 0 , 0 , 0 , 0 , $ancho , $alto );
		return $lienzo;
	}
}
