<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 8:12 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Book;
use AppBundle\Entity\BookPage;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
class BookController extends DefaultController
{
    /**
     * @Route("/{slug}", name="book", defaults={"page" = 1})
     * @Route("/{slug}/page/{page}", name="book_page", defaults={"page" = 1}, requirements={"page": "\d+"})
     * @Template()
     * @param Request $request
     * @param $slug
     * @param $page
     * @return array|RedirectResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function bookAction(Request $request, $slug, $page)
    {
        if (preg_match('/\-$/', $slug)) {
            $slug = mb_substr($slug, 0, mb_strlen($slug, 'utf-8') - 1, 'utf-8');

            if ($request->attributes->get('_route') === 'book') {
                $url = $this->generateUrl('book', ['slug' => $slug]);
            } else {
                $url = $this->generateUrl('book_page', ['slug' => $slug, 'page' => $page]);
            }
            return new RedirectResponse($url, 301);
        }

        $request->attributes->set('_route', 'book_page');

        /** @var Book $book */
        $book = $this->getBookRepository()
            ->createQueryBuilder('b')
            ->where('b.slug = :slug')
            ->setParameters(['slug' => $slug])
            ->getQuery()
            //->useResultCache(true, 3600)
            ->getOneOrNullResult();

        if ($book->getPageCount() === null) {
            $filePath = $this->get('service_container')->getParameter('kernel.root_dir') . '/Resources/_Utf8/' . $book->getFilename();

            $em = $this->get('doctrine.orm.entity_manager');

            $em->transactional(function (EntityManager $em) use ($filePath, $book) {
                $page = 0;

                foreach ($this->readData($filePath) as $arr) {
                    $bookPage = new BookPage();

                    $bookPage->setBook($book);
                    $bookPage->setContent(implode('', $arr));
                    $bookPage->setPage(++$page);

                    $em->persist($bookPage);
                }

                $book->setPageCount($page);

                $em->persist($book);
            });

            $em->refresh($book);
        }

        if ($page < 1 || $page > $book->getPageCount()) {
            throw new NotFoundHttpException('Book page not found');
        }

        /** @var BookPage $bookPage */
        $bookPage = $this->getBookPageRepository()->findOneBy([
            'book' => $book->getId(),
            'page' => $page
        ]);

        return [
            'book' => $book,
            'bookPage' => $bookPage
        ];
    }

    /**
     * @param $filePath
     * @return \Generator
     */
    protected function readData($filePath)
    {
        $h = fopen($filePath, 'r');

        $arr = [];
        $i = 0;

        if ($h !== FALSE) {
            while (($s = fgets($h)) !== FALSE) {
                $arr[] = $s;
                if (++$i === 40) {
                    yield $arr;
                    $arr = [];
                    $i = 0;
                }
            }

            yield $arr;

            fclose($h);
        } else {
            throw new FileException('Cannot read file ' . $filePath);
        }
    }

}