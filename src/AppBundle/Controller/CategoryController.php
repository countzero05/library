<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 8:12 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Category;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Category controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
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
     * @param Request $request
     * @param $slug
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function categoryNameAction(Request $request, $slug)
    {
        /** @var Category $category */
        $category = $this->getCategoryRepository()
            ->createQueryBuilder('c')
            ->where('c.slug = :slug')
            ->orderBy('c.name', 'ASC')
            ->setParameters(['slug' => $slug])
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getOneOrNullResult();

        $authors = null;
        $authorsCount = null;

        if ($category) {
            $authors = $this->getAuthorRepository()
                ->createQueryBuilder('a')
                ->distinct('a.id')
                ->leftJoin('a.books', 'b')
                ->leftJoin('b.books_categories', 'c')
                ->where('c.id = :category')
                ->orderBy('a.name', 'ASC')
                ->setFirstResult(60 * ($request->query->getInt('page', 1) - 1))
                ->setMaxResults(60)
                ->setParameters(['category' => $category->getId()])
                ->getQuery()
                //->useResultCache(true, 3600)
                ->getResult();

            $qb = $this->getAuthorRepository()
                ->createQueryBuilder('a');

            $authorsCount = $this->getAuthorRepository()
                ->createQueryBuilder('a')
                ->select($qb->expr()->countDistinct('a'))
                ->leftJoin('a.books', 'b')
                ->leftJoin('b.books_categories', 'c')
                ->where('c.id = :category')
                ->setParameters(['category' => $category->getId()])
                ->getQuery()
                //->useResultCache(true, 3600)
                ->getSingleScalarResult();
        }


        return [
            'category' => $category,
            'authors' => $authors,
            'authorsCount' => $authorsCount
        ];
    }

}