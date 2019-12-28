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

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Service\FileUploader;
use App\Service\ImageReader;
use App\Service\ImageTransformer;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
	public function indexAction(Session $session)
	{
        $session->start();
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);

        return $this->render('home/index.html.twig', ['form' => $form->createView()]);
	}

    /**
     * @Route("/upload", name="upload", methods={"POST"})
     */
    public function upload(Request $request, ImageUploader $imageUploader)
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form['file']->getData();

            // this condition is needed because the 'image' field is not required
            // so the file must be processed only when a file is uploaded
            if ($imageFile) {
                try {
                    $image->setNumber(0);
                    $imageUploader->upload($imageFile, $image);
                } catch (FileException $e) {
                    throw $e;
                } catch (Exception $e) {
                    throw $e;
                }

            }

            // TODO: Handle error
        }

        return $this->render('home/view-image.html.twig', [
            'form' => $form->createView(),
            'imagen'=> array('nombre_imagen'=>$image->getName(),
                'ancho'	  =>$image->getWidth(),
                'alto'       =>$image->getHeight(),
                'error'	  =>$image->getError()
            ),
        ]);
    }

    /**
     * @Route("view-image-sample", name="viewImageSample", methods={"GET"})
     * @param $session Session
     * @param $entityManager EntityManagerInterface
     * @param $imageReader ImageReader
     * @return Response
     */
    public function viewImageSample(Session $session, EntityManagerInterface $entityManager, ImageReader $imageReader)
    {
		if($id = $session->getId()) {
            /** @var Image $image */
			if ($image = $entityManager->getRepository(Image::class)->find($id)) {
                return $imageReader->read($image->getPath());
            }
		}

        return $imageReader->readDefault();
	}

    /**
     * @Route("submit-image", name="submitImage", methods={"POST"})
     * @param $session Session
     * @param $entityManager EntityManagerInterface
     */
    public function submitImage(Session $session, EntityManagerInterface $entityManager)
    {
        if($id = $session->getId()) {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($id);

            return $this->render('home/submit-image.html.twig', array(
                'image' => $image,
                'ruta_imagen' => $image->getPath(),
                'ancho' => $image->getHeight(),
                'alto' => $image->getWidth(),
                'error' => $image->getError(),
            ));
        } else {
            return $this->render('home/index.html.twig');
        }
    }

    /**
     * @Route("save", name="save", methods={"POST"})
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     */
    public function save(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        if($session->getId()) {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($session->getId());
            // open the file in a binary mode
            $fp = fopen($image->getPath(), 'rb');
            header("Content-Type: image/".$image->getExtension());
            //header("Content-Length: ".$imagen->getSize());
            header('Content-Disposition: attachment; filename="imagen.' . $image->getExtension() . '"');
            // dump the picture and stop the script
            fpassthru($fp);
            exit;
        }
    }

    /**
     * @Route("back", name="back", methods={"GET"})
     */
    public function back()
    {
        //TODO
    }

    // *************** BASIC ***************//

    /**
     * @Route("change-dimensions", name="changeDimensions", methods={"POST"})
     * @param $request Request
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     * @param $imageTransformer ImageTransformer
     */
    public function changeDimensions(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
	{
		if($session->getId()) {
            /** @var Image $image */
			$image = $entityManager->getRepository(Image::class)->find($session->getId());
			if ($request->getMethod() == 'POST') {
				$iNewWidth = (int) $request->request->get('dimensionesX');
				$iNewHeight = (int) $request->request->get('dimensionesY');

				$image = $imageTransformer->changeDimensions($image, $iNewWidth, $iNewHeight);

				return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());

			}
		}

		return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
	}

    /**
     * @Route("crop", name="crop", methods={"POST"})
     * @param $request Request
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     * @param $imageTransformer ImageTransformer
     */
    public function crop(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
    {
        if($session->getId())
        {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($session->getId());

            if ($request->getMethod() == 'POST') {
                $cropLeft = (int) $request->request->get('recortar_izq');
                $cropRight = (int) $request->request->get('recortar_der');
                $cropTop = (int) $request->request->get('recortar_arr');
                $cropBottom = (int) $request->request->get('recortar_aba');

                $image = $imageTransformer->crop($image, $cropTop, $cropRight, $cropBottom, $cropLeft);

                return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());
            }

        }
        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }

    /**
     * @Route("change-brightness", name="changeBrightness", methods={"POST"})
     * @param $request Request
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     * @param $imageTransformer ImageTransformer
     */
    public function changeBrightness(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
    {
        if($session->getId())
        {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($session->getId());

            if ($request->getMethod() == 'POST') {
                $brightness = (int) $request->request->get('brillo');

                $image = $imageTransformer->changeBrightness($image, $brightness);

                return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());
            }

        }
        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }

    /**
     * @Route("change-contrast", name="changeContrast", methods={"POST"})
     * @param $request Request
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     * @param $imageTransformer ImageTransformer
     */
    public function changeContrast(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
    {
        if($session->getId())
        {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($session->getId());

            if ($request->getMethod() == 'POST') {
                $contrast = (int) $request->request->get('contraste');

                $image = $imageTransformer->changeContrast($image, $contrast);

                return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());
            }

        }
        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }

    /**
     * @Route("rotate", name="rotate", methods={"POST"})
     * @param $request Request
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     * @param $imageTransformer ImageTransformer
     */
    public function rotate(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
    {
        if($session->getId())
        {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($session->getId());

            if ($request->getMethod() == 'POST') {
                $rotation = (int) $request->request->get('rotacion');

                $image = $imageTransformer->rotate($image, $rotation);

                return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());
            }

        }
        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }

    // ************ END BASIC ***************//

    // ************* FILTERS *************** //

    /**
     * @Route("gray-scale", name="grayScale", methods={"POST"})
     * @param $request Request
     * @param $session SessionInterface
     * @param $entityManager EntityManagerInterface
     * @param $imageTransformer ImageTransformer
     */
    public function grayScale(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
    {
        if($session->getId())
        {
            /** @var Image $image */
            $image = $entityManager->getRepository(Image::class)->find($session->getId());

            if ($request->getMethod() == 'POST') {

                $image = $imageTransformer->grayScale($image);

                return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());
            }

        }
        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }

    // *********** END FILTERS ************* //

    protected function renderTemplateViewChanges($width, $height, $error)
	{
		return $this->render('home/view-changes.html.twig', array(
                'image' => array(
                    'dimx' => $width,
                    'dimy' => $height,
                    'error' => $error
				)
            )
        );
	}
}
