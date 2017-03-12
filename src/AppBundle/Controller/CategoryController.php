<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Category controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
 * @Route("/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="category")
     * @Template()
     */
    public function categoryAction()
    {
        $categories = $this->getDoctrine()->getRepository('AppBundle:Category')
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

    /**
     * @Route("/{slug}", name="category_name")
     * @Template()
     * @param Request $request
     * @param Category $category
     * @return array
     * @ParamConverter("category", options={"mapping": {"slug": "slug"}})
     */
    public function categoryNameAction(Request $request, Category $category)
    {
        list ($authors, $count) = $this->getDoctrine()->getRepository('AppBundle:Author')->getAuthorsByCategoryAndPage($category, $request->query->getInt('page', 1));

        return [
            'category' => $category,
            'authors' => $authors,
            'authorsCount' => $count
        ];
    }

}