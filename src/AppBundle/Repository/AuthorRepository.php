<?php

namespace AppBundle\Repository;


use AppBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository
{

    /**
     * @param Category $category
     * @param int $page
     * @return array
     */
    public function getAuthorsByCategoryAndPage(Category $category, int $page = 1)
    {
        $qb = $this->createQueryBuilder('author');

        $count = $qb
            ->select($qb->expr()->countDistinct('author'))
            ->leftJoin('author.books', 'books')
            ->leftJoin('books.books_categories', 'categories')
            ->where('categories.id = :category')
            ->setParameters(['category' => $category->getId()])
            ->getQuery()
            //->useResultCache(true, 3600)
            ->getSingleScalarResult();

        $authors = $qb
            ->select('author')
            ->distinct('author.id')
//            ->leftJoin('author.books', 'books')
//            ->leftJoin('books.books_categories', 'categories')
//            ->where('categories.id = :category')
            ->orderBy('author.name', 'ASC')
            ->setFirstResult(60 * ($page - 1))
            ->setMaxResults(60)
            ->setParameters(['category' => $category->getId()])
            ->getQuery()
            //->useResultCache(true, 3600)
            ->getResult();


        return [$authors, $count];
    }

    public function getAuthorsByLetterAndPage(string $letter, int $page = 1)
    {
        $qb = $this
            ->createQueryBuilder('author');

        $count = $qb
            ->select('count(author.name)')
            ->where('author.name like :letter')
            ->getQuery()
            ->setParameters(['letter' => $letter . '%'])
            ->useResultCache(true, 3600)
            ->getSingleScalarResult();

        $authors = $qb
            ->select('author')
            ->orderBy('author.name', 'ASC')
            ->setMaxResults(60)
            ->setFirstResult(60 * ($page - 1))
            ->getQuery()
            ->setParameters(['letter' => $letter . '%'])
            ->useResultCache(true, 3600)
            ->getResult();

        return [$authors, $count];
    }

}