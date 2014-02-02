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
use Symfony\Component\HttpFoundation\Request;

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
	
	public function cambiarDimensionesAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			if ($request->getMethod() == 'POST') {
					$iNewWidth = $request->request->get('dimensionesX');
					$iNewHeight = $request->request->get('dimensionesY');
				  
				$canvas = $imagen->changeDimensions( $iNewWidth, $iNewHeight, 10000, 10000 );
				
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
				
			} 
		} 
		
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
		
		
	}
	
	public function recortarAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
				if ($request->getMethod() == 'POST') {
					$cropLeft = $request->request->get('recortar_izq');
					$cropRight = $request->request->get('recortar_der');
					$cropTop = $request->request->get('recortar_arr');
					$cropBottom = $request->request->get('recortar_aba');
					
					$canvas = $imagen->cropImage( $cropLeft, $cropRight, $cropTop, $cropBottom );
					
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
					
					return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
					
				} 
			 
		}	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}		


	public function cambiarBrilloAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			if ($request->getMethod() == 'POST') {
				$brightness = $request->request->get('brillo');

				$canvas = $imagen->filterImage( $brightness, IMG_FILTER_BRIGHTNESS, 255, -255 );	
													
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
				
			} 
			 
		}	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}		

	public function cambiarContrasteAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			if ($request->getMethod() == 'POST') {
				$contraste = $request->request->get('contraste');
				
				$canvas = $imagen->filterImage( $contraste, IMG_FILTER_CONTRAST, 1000, -1000 );
									
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
				
			} 
			 
		}	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}			

	public function rotarAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			if ($request->getMethod() == 'POST') {
				$rotacion = $request->request->get('rotacion');
				
				$canvas = $imagen->rotateImage( $rotacion );
										
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
				
			} 
			 
		}	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function cambiarAGrisesAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_GRAYSCALE );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
		
	
	public function cambiarANegativoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_NEGATE );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
									
	public function resaltarBordesAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_EDGEDETECT );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function resaltarRelieveAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_EMBOSS );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function eliminacionMediaAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_MEAN_REMOVAL );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}

	public function borrosoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_SELECTIVE_BLUR );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function borrosogaussAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->filterImage( 0, IMG_FILTER_GAUSSIAN_BLUR );
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function suavizadoAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$suavizado = $request->request->get('suavizado');
					
				$canvas = $imagen->filterImage( $suavizado, IMG_FILTER_SMOOTH, 5000, -5000 );
									
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function pixelacionAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$pixelacion = $request->request->get('pixelacion');
					
				$canvas = $imagen->filterImage( $pixelacion, IMG_FILTER_PIXELATE, 1000000, 0 );
									
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function convolucionAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$matriz= array(array(3), array(3), array(3));
				for($i=0;$i<=8;$i++) {
					$value = $request->request->get('convolucion_matriz_'.$i);

					if($i<3) $matriz[0][$i] = $value;
					elseif($i<6) $matriz[1][$i-3] = $value;
					elseif($i<9) $matriz[2][$i-6] = $value;
				}
				
				$divisor = $request->request->get('convolucion_divisor');
				$offset = $request->request->get('convolucion_offset');
				
				$canvas = $imagen->convolutionFilterImage( $matriz, $divisor, $offset );

				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}

	public function correcciongammaAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$entrada = $request->request->get('entrada_gamma');				
				$salida = $request->request->get('salida_gamma');
					
				$canvas = $imagen->gammaCorrectionImage( $entrada, $salida );
									
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}

	public function colorearAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$rojo = $request->request->get('colorear_r');				
				$verde = $request->request->get('colorear_g');
				$azul = $request->request->get('colorear_b');
				$alfa = $request->request->get('alfa');
					
				$canvas = $imagen->colorizeImage( $rojo, $verde, $azul, $alfa );
									
				if( $imagen->getError() == "" )
				{
					$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
				} 
				
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function resaltarcoloresAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$inputRojo = $request->request->get('resaltar_colores_r');				
				$inputVerde = $request->request->get('resaltar_colores_g');
				$inputAzul = $request->request->get('resaltar_colores_b');
				
				
				if( $inputRojo == 'check' ) {
					$canvas = $imagen->highlightRedImage();
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
				}
				
				if(  $inputVerde == 'check' ) {
					$canvas = $imagen->highlightGreenImage();
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
				}
				
				if(  $inputAzul == 'check' ) {
					$canvas = $imagen->highlightBlueImage();
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
				}
									
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());	
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}	
	
	public function atenuarcoloresAction(Request $request)
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);
			
			if ($request->getMethod() == 'POST') {
				
				$inputRojo = $request->request->get('atenuar_colores_r');				
				$inputVerde = $request->request->get('atenuar_colores_g');
				$inputAzul = $request->request->get('atenuar_colores_b');
				
				
				if( $inputRojo == 'check' ) {
					$canvas = $imagen->attenuateRedImage();
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
				}
				
				if(  $inputVerde == 'check' ) {
					$canvas = $imagen->attenuateGreenImage();
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
				}
				
				if(  $inputAzul == 'check' ) {
					$canvas = $imagen->attenuateBlueImage();
					if( $imagen->getError() == "" )
					{
						$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
					} 
				}
									
				return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());	
			}
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}	
	
	public function lapizsuperfinoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->superthinpencilEffect();
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function lapizfinoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->thinpencilEffect();
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function lapiznormalAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->regularpencilEffect();
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	
	public function lapizgruesoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->thickpencilEffect();
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function efectopinturaAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->paintEffect();
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function efectoCheAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->cheGuevaraEffect();
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}

	public function papelarrugadoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('wrinkledPaper');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function antiguoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('old');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function fuegoAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('fire');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function gotasAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('drops');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function lucesAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('lights');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function coloresAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('colors');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function molonAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('cool');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function marcohorizontalAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('horizontal_frame');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	public function marcoverticalAction()
	{
		if( isset($_SESSION['id']) )
		{		
			$em = $this->getDoctrine()->getEntityManager();
			$imagen = $em->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id']);

			$canvas = $imagen->overlapEffect('vertical_frame');
								
			if( $imagen->getError() == "" )
			{
				$imagen = $this->createDeletePersistImage($imagen, $canvas, $em);
			} 
			
			return $this->renderTemplateVerCambios($imagen->getAncho(), $imagen->getAlto(), $imagen->getError());
			
		} 
			 	
		return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}
	
	
	
	protected function renderTemplateVerCambios($ancho, $alto, $error) 
	{
		return $this->render(
					'MSDHomeBundle:Home:vercambios.html.twig',
					array(
					'imagen' => array('dimx' => $ancho,
									  'dimy' => $alto,
									  'error' => $error
					)
					)
					);
	}
	
	protected function createDeletePersistImage($imagen, $lienzo, $em)
	{
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
			
			return $imagen;
	}	

	
	protected function createOriginalCanvas($imagen) 
	{
		if($original = $this->crearLienzo($imagen->getRuta(), $imagen->getFormato())) return $original;
		else return false;
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
