<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 8:12 PM
 */

namespace AppBundle\Controller;


use AppBundle\Controller\DefaultController;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Author controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
 * @Route("/author")
 */
class AuthorController extends DefaultController
{
    /**
     * @Route("/", name="author", defaults={"letter"="А"})
     * @Route("/letter/{letter}", name="author_letter", defaults={"letter"="А"})
     * @Template()
     */
    public function authorAction(Request $request, $letter)
    {
        $authors = $this->getAuthorRepository()
            ->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->where('a.name like :letter')
            ->getQuery()
            ->setParameters(['letter' => $letter . '%'])
            ->useResultCache(true, 3600)
            ->getResult();

        $allLetters = array();
        foreach (range(chr(0xC0), chr(0xDF)) as $v) {
            $l = iconv('CP1251', 'UTF-8', $v);
            if (in_array($l, ['Ы', 'Ъ', 'Ь']))
                continue;
            $allLetters[$l] = $l;
        }

        return [
            'authors' => $authors,
            'allLetters' => $allLetters
        ];
    }

    /**
     * @Route("/{slug}", name="author_name")
     * @Template()
     */
    public function authorNameAction($slug)
    {
        $author = $this->getAuthorRepository()
            ->createQueryBuilder('a')
            ->where('a.slug = :slug')
            ->setParameters(['slug' => $slug])
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getOneOrNullResult();

        return [
            'author' => $author
        ];
    }

}