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

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MSD\HomeBundle\Entity\Imagen as Imagen;
use MSD\HomeBundle\Controller\HomeController as HomeController;
use Symfony\Component\DependencyInjection\ContainerAware;

class ImageTransController extends ContainerAware
{
	protected $em ;
	private $imagen;
	private $original;
	private $lienzo;

	function __construct(EntityManager $em, ContainerAware $container)
	{
		 //$this->em = $em;
		$this->setEm( $em );
		if($this->getImageRepository($this->getEm()))
		{
			$this->setContainer( $container );
			if($this->createOriginalCanvas()) return;
		}
		$this->getImagen->setError('No se pudo crear la nueva imagen');						
	}
	
	public function cambiarDimensionesAction()
	{
		if($this->getImagen()->getError() == '')
		{					
			//Comprobar que los datos recibidos son correctos
			if(isset($_POST['dimensionesX']) && isset($_POST['dimensionesY'])) {
				$dimx=$_POST['dimensionesX'];
				$dimy=$_POST['dimensionesY'];
				if($dimx && $dimy) {
					if(is_numeric($dimx) && is_numeric($dimy)) {
						if(($dimx<=$this->getImagen()->getAncho()+1000) && ($dimy<=$this->getImagen()->getAlto()+1000) && $dimx>=0 && $dimy>=0) {
							if(($dimx<=6000) && ($dimy<=6000)) {
		
								//cambiar las dimensiones de la imagen
								
								//Crear lienzo en blanco con proporciones
								$this->setLienzo( imagecreatetruecolor($dimx,$dimy) );
	
								// preserve transparency
								if($this->getImagen()->getFormato() == "gif" or $this->getImagen()->getFormato() == "png"){
									imagecolortransparent($this->getLienzo(), imagecolorallocatealpha($this->getLienzo(), 0, 0, 0, 127));
								    imagealphablending($this->getLienzo(), false);
								    imagesavealpha($this->getLienzo(), true);
								}
								
								//Copiar $this->original sobre la imagen que acabamos de crear en blanco ($tmp)
								imagecopyresampled($this->getLienzo(),$this->getOriginal(),0,0,0,0,$dimx, $dimy,$this->getImagen()->getAncho(),$this->getImagen()->getAlto());
								
								$this->getImagen()->setError('');
								$this->getImagen()->setAlto($dimy);
								$this->getImagen()->setAncho($dimx);
								
							} else $this->getImagen()->setError('Las dimensiones introducidas son demasiado grandes. Máximo 6000 x 6000');
						} else $this->getImagen()->setError('Las dimensiones introducidas son demasiado grandes. Máximo escalas de +1000');
					} else $this->getImagen()->setError('Los datos introducidos no son enteros');
				} else $this->getImagen()->setError('Los datos introducidos no pueden ser nulos');
			} else $this->getImagen()->setError('No se han cargado datos');
		} else $this->getImagen()->setError('No se pudo crear la nueva imagen');	
			
		if($this->getImagen()->getError() == '')		
		{
			$this->setImagen( $this->createDeletePersistImage($this->getImagen(), $this->getLienzo(), $this->getOriginal(), $this->getEm()));
			return $this->renderTemplateVerCambios($this->getImagen()->getAncho(), $this->getImagen()->getAlto(), $this->getImagen()->getError());
		} else {
			return $this->renderTemplateVerCambios(0, 0, 'tu sesión ha caducado. Vuelve a probar');
		}
	}
	

	protected function getImageRepository($em="")
	{ 
		if($this->setImagen( $this->getEm()->getRepository('MSDHomeBundle:Imagen')->find($_SESSION['id'])) ) return $this;
		else return false;
	}
	
	protected function createOriginalCanvas($imagen = "") 
	{
		if($this->setOriginal( $this->crearLienzo($this->getImagen()->getRuta(), $this->getImagen()->getFormato())) && $this->setLienzo( $this->crearLienzo($this->getImagen()->getRuta(), $this->getImagen()->getFormato())) ) return true;
		else return false;
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
	
	
	public function getEm() 
	{
		return $this->em;
	}
	public function setEm($em) 
	{
		$this->em = $em;
		return $this;
	}
	public function getLienzo() 
	{
		return $this->lienzo;
	}
	public function setLienzo($lienzo) 
	{
		$this->lienzo = $lienzo;
		return $this;
	}
	public function getOriginal() 
	{
		return $this->original;
	}
	public function setOriginal($original) 
	{
		$this->original = $original;
		return $this;
	}
	public function getImagen() 
	{
		return $this->imagen;
	}
	public function setImagen($imagen) 
	{
		$this->imagen = $imagen;
		return $this;
	}
}
