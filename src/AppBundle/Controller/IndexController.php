<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 10:28 PM
 */

namespace AppBundle\Controller;


use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Index controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
 * @Route("/")
 */
class IndexController extends DefaultController
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $categories = $this->getCategoryRepository()
            ->createQueryBuilder('c')
            ->where('c.parent is null')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getResult();

        return [
            'categories' => $categories
        ];
    }

}