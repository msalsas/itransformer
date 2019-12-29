<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Service\ImageReader;
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
}
