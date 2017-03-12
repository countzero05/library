<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Author;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Author controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
 * @Route("/author")
 */
class AuthorController extends Controller
{
    /**
     * @Route("/", name="author", defaults={"letter"="А"})
     * @Route("/letter/{letter}", name="author_letter")
     * @Template()
     * @param Request $request
     * @param $letter
     * @return array
     */
    public function authorAction(Request $request, $letter)
    {

        list ($authors, $count) = $this->getDoctrine()->getRepository('AppBundle:Author')->getAuthorsByLetterAndPage($letter, $request->query->getInt('page', 1));

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
            'authorsCount' => $count
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