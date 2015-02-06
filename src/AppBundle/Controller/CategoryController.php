<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 8:12 PM
 */

namespace AppBundle\Controller;


use AppBundle\Controller\DefaultController;
use AppBundle\Entity\Category;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Category controller.
 *
 * @Route("/category")
 */
class CategoryController extends DefaultController
{
    /**
     * @Route("/", name="category")
     * @Template()
     */
    public function categoryAction()
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

    /**
     * @Route("/{slug}", name="category_name")
     * @Template()
     */
    public function categoryNameAction($slug)
    {
        /** @var Category $category */
        $category = $this->getCategoryRepository()
            ->createQueryBuilder('c')
            ->addSelect('b')
            ->addSelect('a')
            ->leftJoin('c.books', 'b')
            ->leftJoin('b.author', 'a')
            ->where('c.slug = :slug')
            ->orderBy('c.name', 'ASC')
            ->setParameters(['slug' => $slug])
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getOneOrNullResult();

        return [
            'category' => $category
        ];
    }

}