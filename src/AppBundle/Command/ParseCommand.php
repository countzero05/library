<?php

namespace AppBundle\Command;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends ContainerAwareCommand
{

    /**
     * @var Book[]
     */
    protected $books = [];

    /**
     * @var Author[]
     */
    protected $authors = [];

    /**
     * @var Category[]
     */
    protected $categories = [];

    protected $root_dir = '';

    /**
     * @var EntityManager
     */
    protected $manager;

    /**
     * @return Registry
     */
    protected function getDoctrine()
    {
        return $this->getContainer()->get('doctrine');
    }

    /**
     * @return ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @var string[]
     */
    protected static $excludes = [
//        'Военная проза и мемуары',
//        'Зарубежная проза',
//        'Зарубежный детектив',
        'Любовная литература',
        'Приключения и история',
//        'Русская проза',
//        'Русский детектив',
        'Эротика и секс',
//        'Детская литература',
//        'Зарубежная фантастика',
//        'Классика',
//        'Научно-популярная литература',
        'Разное',
//        'Русская фантастика',
        'Стихи и песни',
        'Юмор'
    ];

    protected function configure()
    {
        $this
            ->setName('library:parse')
            ->setDescription('Parse library catalog');
    }

    protected function hasChildrenDirs($path)
    {
        foreach (glob($path . '/*') as $filename) {
            if (is_dir($filename))
                return true;
        }

        return false;
    }

    private function shouldExclude($filename) {
        $name = basename($filename);

        foreach (self::$excludes as $exclude) {
            if (mb_strpos(mb_strtolower($name), mb_strtolower($exclude)) !== false) {
                return true;
            }
        }

        return false;
    }

    private function saveStructure($root, Category &$parentCategory = null, Author &$author = null)
    {
        foreach (glob($root . '/*') as $filename) {
            if (is_dir($filename)) {
                if ($this->shouldExclude($filename)) {
                    continue;
                }
                if ($this->hasChildrenDirs($filename)) {
                    $category = new Category();
                    $category->setName(basename($filename));
                    $category->setParent($parentCategory);
                    $category->setSlug($this->createCategorySlug($category));
                    $this->categories[$category->getSlug()] = $category;
                    $this->saveStructure($filename, $category);
                } else {
                    $slug = $this->createSlug(basename($filename));

                    if (isset($this->authors[$slug])) {
                        $author = $this->authors[$slug];
                    } else {
                        $author = new Author();
                        $author->setName(basename($filename));
                        $author->setSlug($slug);
                        $this->authors[$author->getSlug()] = $author;
                    }
                    $this->saveStructure($filename, $parentCategory, $author);
                }
            } else {
                if (!$author) {
                    echo "Excluding $filename from list \n";

                    return;
                }

                $name = basename($filename, '.txt');

                $slug = $this->createSlug($author->getName() . ' ' . $name);

                if (!isset($this->books[$slug])) {
                    $book = new Book();
                    $book->setName($name);
                    $book->setAuthor($author);
                    $book->setSlug($slug);
                    $book->setFilename(mb_substr($filename, mb_strlen($this->root_dir, 'utf-8'), null, 'utf-8'));
                    $this->books[$slug] = $book;
                }

                $this->books[$slug]->addCategory($parentCategory);
                $parentCategory->addBook($this->books[$slug]);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $libraryDir = $this->getContainer()->getParameter('library_directory');

        if (!file_exists($libraryDir))
            throw new \Exception('Path to folder with files does not exist');

        $this->root_dir = $libraryDir;

        $this->manager = $this->getManager();

        $this->manager->transactional(function () use ($libraryDir, $output) {
            $output->writeln('Parsing directory structure');
            $this->saveStructure($libraryDir);
            $output->writeln('Saving categories and authors');

            /** @var PDOConnection $con */
            $con = $this->getDoctrine()->getConnection()->getWrappedConnection();

            $aCmd = $con->prepare('INSERT INTO authors (id, name, slug, created, updated) VALUES (nextval(\'authors_id_seq\'), :name, :slug, current_timestamp, current_timestamp)');
            $cCmd = $con->prepare('INSERT INTO categories (id, parent_id, name, slug, created, updated) VALUES (nextval(\'authors_id_seq\'), (SELECT id FROM categories WHERE slug = :parent_slug LIMIT 1), :name, :slug, current_timestamp, current_timestamp)');
            $bCmd = $con->prepare('INSERT INTO books (id, author_id, name, slug, filename, created, updated) VALUES (nextval(\'authors_id_seq\'), (SELECT id FROM authors WHERE slug = :author_slug LIMIT 1), :name, :slug, :filename, current_timestamp, current_timestamp)');
            $assocCmd = $con->prepare('INSERT INTO books_categories (book_id, category_id) VALUES ((SELECT id FROM books WHERE slug = :book_slug LIMIT 1), (SELECT id FROM categories WHERE slug = :category_slug LIMIT 1))');

            foreach ($this->authors as $author) {
                $aCmd->execute([
                    'name' => $author->getName(),
                    'slug' => $author->getSlug(),
                ]);
            }

            foreach ($this->categories as $category) {
                $cCmd->execute([
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'parent_slug' => $category->getParent() ? $category->getParent()->getSlug() : '',
                ]);
            }

            foreach ($this->books as $book) {
                $bCmd->execute([
                    'name' => $book->getName(),
                    'slug' => $book->getSlug(),
                    'filename' => $book->getFilename(),
                    'author_slug' => $book->getAuthor()->getSlug()
                ]);

                /** @var Category $category */
                foreach ($book->getBooksCategories() as $category) {
                    $assocCmd->execute([
                        'category_slug' => $category->getSlug(),
                        'book_slug' => $book->getSlug()
                    ]);
                }
            }


        });

        $output->writeln('Parsing successfully completed');
    }

    protected function createBookSlug(Book $book)
    {
        return $this->createSlug($book->getAuthor()->getName() . ' ' . $book->getName());
    }

    protected function createCategorySlug(Category $category)
    {
        $phrase = '';
        do {
            if ($phrase)
                $phrase = $category->getName() . ' ' . $phrase;
            else
                $phrase = $category->getName();

        } while ($category = $category->getParent());

        return $this->createSlug($phrase);
    }

    protected function createSlug($phrase)
    {
        $phrase = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $phrase);

        $phrase = str_replace('\'', '', $phrase);
        $phrase = str_replace('ʹ', '', $phrase);
        $phrase = str_replace('`', '', $phrase);
        $phrase = preg_replace('/[^a-z\d]/', '-', $phrase);

        while (strpos($phrase, '--') !== false) {
            $phrase = str_replace('--', '-', $phrase);
        }

        return trim($phrase, " -\t\n\r\0\x0B");
    }

}