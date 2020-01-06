<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class AboutController extends BaseImageController
{
    /**
     * @Route("/about", name="about", methods={"GET"})
     */
    public function aboutAction()
    {
        return $this->render('about/index.html.twig');
    }
}
