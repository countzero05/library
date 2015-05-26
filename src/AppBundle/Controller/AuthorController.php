<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 8:12 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Author;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @param Request $request
     * @param $letter
     * @return array
     */
    public function authorAction(Request $request, $letter)
    {
        $authors = $this->getAuthorRepository()
            ->createQueryBuilder('a')
            ->orderBy('a.name', 'ASC')
            ->where('a.name like :letter')
            ->setMaxResults(60)
            ->setFirstResult(60 * ($request->query->getInt('page', 1) - 1))
            ->getQuery()
            ->setParameters(['letter' => $letter . '%'])
            ->useResultCache(true, 3600)
            ->getResult();

        $authorsCount = $this->getAuthorRepository()
            ->createQueryBuilder('a')
            ->select('count(a)')
            ->where('a.name like :letter')
            ->getQuery()
            ->setParameters(['letter' => $letter . '%'])
            ->useResultCache(true, 3600)
            ->getSingleScalarResult();

        $allLetters = array();
        foreach (range(chr(0xC0), chr(0xDF)) as $v) {
            $l = iconv('CP1251', 'UTF-8', $v);
            if (in_array($l, ['Ы', 'Ъ', 'Ь']))
                continue;
            $allLetters[$l] = $l;
        }

        return [
            'authors' => $authors,
            'allLetters' => $allLetters,
            'authorsCount' => $authorsCount
        ];
    }

    /**
     * @Route("/{slug}", name="author_name")
     * @ParamConverter("author", options={"mapping": {"slug": "slug"}})
     * @Cache(lastModified="author.getUpdated()", ETag="'Author' ~ author.getId() ~ author.getUpdated().format('r')")
     * @Template()
     * @param Author $author
     * @return array
     */
    public function authorNameAction(Author $author)
    {
        return [
            'author' => $author
        ];
    }

    /**
     * @Route("/{slug}/biography", name="author_biography")
     * @ParamConverter("author", options={"mapping": {"slug": "slug"}})
     * @Cache(lastModified="author.getBiographyUpdated()", ETag="'Author' ~ author.getId() ~ author.getBiographyUpdated().format('r')")
     * @Template()
     * @param Author $author
     * @return array
     */
    public function biographyAction(Author $author)
    {
        return [
            'author' => $author
        ];
    }

}