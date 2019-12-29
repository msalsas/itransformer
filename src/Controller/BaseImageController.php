<?php

namespace App\Controller;

use App\Entity\Image;
use App\Exception\ImageTransformerException;
use App\Service\ImageTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BaseImageController extends AbstractController
{
    /**
     * Request
     */
    protected $request;
    /**
     * SessionInterface
     */
    protected $session;
    /**
     * EntityManagerInterface
     */
    protected $entityManager;
    /**
     * ImageTransformer
     */
    protected $imageTransformer;

    public function __construct(RequestStack $requestStack, SessionInterface $session, EntityManagerInterface $entityManager, ImageTransformer $imageTransformer)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->imageTransformer = $imageTransformer;
    }

    protected function findAndRenderImage($callback)
    {
        if($this->session->getId()) {
            try {
                /** @var Image $image */
                $image = $this->entityManager->getRepository(Image::class)->find($this->session->getId());
                if ($this->request->getMethod() == 'POST') {
                    if (is_callable($callback)) {
                        $image = $callback($image);
                    }

                    return $this->renderTemplateViewChanges($image->getWidth(), $image->getHeight(), $image->getError());

                }
            } catch(ImageTransformerException $e) {
                return $this->renderTemplateViewChanges(0, 0, $e->getMessage());
            } catch(\Exception $e) {
                return $this->renderTemplateViewChanges(0, 0, 'Unexpected error');
            }
        }

        return $this->renderTemplateViewChanges(0, 0, 'tu sesión ha caducado. Vuelve a probar');
    }

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