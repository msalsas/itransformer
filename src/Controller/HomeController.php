<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepositoryInterface;
use App\Service\ImageReader;
use App\Service\ImageUploader;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseImageController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
	public function indexAction()
	{
        $this->session->start();
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);

        return $this->render('home/index.html.twig', ['form' => $form->createView()]);
	}

    /**
     * @Route("/upload", name="upload", methods={"POST"})
     * @param $imageUploader ImageUploader
     */
    public function upload(ImageUploader $imageUploader)
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($this->request);

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
     * @param $imageReader ImageReader
     * @return Response
     */
    public function viewImageSample(ImageReader $imageReader)
    {
		if($id = $this->session->getId()) {
            /** @var Image $image */
			if ($image = $this->entityManager->getRepository(Image::class)->find($id)) {
                return $imageReader->read($image->getPath());
            }
		}

        return $imageReader->readDefault();
	}

    /**
     * @Route("submit-image", name="submitImage", methods={"POST"})
     */
    public function submitImage()
    {
        if($id = $this->session->getId()) {
            /** @var Image $image */
            $image = $this->entityManager->getRepository(Image::class)->find($id);

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
     */
    public function save()
    {
        if($this->session->getId()) {
            /** @var Image $image */
            $image = $this->entityManager->getRepository(Image::class)->find($this->session->getId());
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
     * @Route("back", name="back", methods={"POST"})
     * @param $imageUploader ImageUploader
     */
    public function back(ImageUploader $imageUploader)
    {
        if($sessionId = $this->session->getId()) {
            try {
                /** @var ImageRepositoryInterface $repository */
                $repository = $this->entityManager->getRepository(Image::class);
                /** @var Image $image */
                $image = $repository->find($sessionId);
                if ($this->request->getMethod() == 'POST') {

                    if ($image->getNumber() > 0) {
                        $imageUploader->delete($image);
                        $image = $repository->find($sessionId);
                    }
                    return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());

                }
            } catch(\Exception $e) {
                return $this->renderTemplateViewChanges(0, 0, 'Unexpected error');
            }
        }

        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }
}
