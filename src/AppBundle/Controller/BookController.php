<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 1/10/15
 * Time: 8:12 PM
 */

namespace AppBundle\Controller;


use AppBundle\Controller\DefaultController;
use AppBundle\Entity\Book;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
     * @Route("/{slug}", name="book")
     * @Template()
     */
    public function bookAction($slug)
    {
        /** @var Book $book */
        $book = $this->getBookRepository()
            ->createQueryBuilder('b')
            ->where('b.slug = :slug')
            ->setParameters(['slug' => $slug])
            ->getQuery()
            ->useResultCache(true, 3600)
            ->getOneOrNullResult();

        $content = file_get_contents($this->get('service_container')->getParameter('kernel.root_dir') . '/Resources/_Utf8/' . $book->getFilename());

        return [
            'book' => $book,
            'content' => $content
        ];
    }

}