<?php

namespace AppBundle\Repository;


use AppBundle\Entity\Category;
use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{

    /**
     * @return Category[]
     */
    public function getRootDirectories()
    {
        return $this->createQueryBuilder('category')
            ->where('category.parent is null')
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getResult();
    }

}