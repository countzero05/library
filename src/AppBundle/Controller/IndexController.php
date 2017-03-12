<?php

namespace AppBundle\Controller;


use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Index controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
 * @Route("/")
 */
class IndexController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return [
            'categories' => $this->getDoctrine()->getRepository('AppBundle:Category')->getRootDirectories()
        ];
    }

}