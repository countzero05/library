<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Book;
use AppBundle\Entity\BookPage;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BookRepository extends EntityRepository
{

    public function parseBook(Book $book, string $dir)
    {
        $page = 0;
        $em = $this->getEntityManager();

        $em->beginTransaction();
        try {
            foreach ($this->readData($dir . '/' . $book->getFilename()) as $arr) {
                $bookPage = new BookPage();

                $bookPage->setBook($book);
                $bookPage->setContent(implode('', $arr));
                $bookPage->setPage(++$page);

                $book->getBooksPages()->add($bookPage);
            }

            $book->setPageCount();
            $em->merge($book);
            $em->flush();

            $em->commit();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die();
            $em->rollback();
        }
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