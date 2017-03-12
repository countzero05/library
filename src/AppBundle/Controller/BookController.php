<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Book;
use AppBundle\Entity\BookPage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Book controller.
 *
 * @Cache(expires="+1 minute", public="true", smaxage="60")
 * @Route("/book")
 */
class BookController extends Controller
{
    /**
     * @Route("/{slug}", name="book", defaults={"page" = 1})
     * @Route("/{slug}/page/{page}", name="book_page", defaults={"page" = 1}, requirements={"page": "\d+"})
     * @Template()
     * @param Request $request
     * @param Book $book
     * @param $page
     * @return array|RedirectResponse
     * @ParamConverter("book", options={"mapping": {"slug": "slug"}})
     */
    public function bookAction(Request $request, Book $book, int $page = 1)
    {
        $request->attributes->set('_route', 'book_page');

        if (!$book->getPageCount()) {
            $this->getDoctrine()->getRepository('AppBundle:Book')
                ->parseBook($book, $this->getParameter('library_directory'));
        }

        if ($page < 1 || $page > $book->getPageCount()) {
            throw new NotFoundHttpException('Book page not found');
        }

        /** @var BookPage $bookPage */
        $bookPage = $this->getDoctrine()->getRepository('AppBundle:BookPage')->findOneBy([
            'book' => $book->getId(),
            'page' => $page
        ]);

        return [
            'book' => $book,
            'bookPage' => $bookPage
        ];
    }

}