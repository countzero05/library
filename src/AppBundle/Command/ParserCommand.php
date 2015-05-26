<?php

namespace AppBundle\Command;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Driver\PDOConnection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParserCommand extends ContainerAwareCommand
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
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    protected static $directories = [
//        'Военная проза и мемуары',
//        'Зарубежная проза',
//        'Зарубежный детектив',
//        'Любовная литература',
//        'Приключения и история',
//        'Русская проза',
//        'Русский детектив',
//        'Эротика и секс',
//        'Детская литература',
        'Зарубежная фантастика',
//        'Классика',
//        'Научно-популярная литература',
//        'Разное',
        'Русская фантастика',
//        'Стихи и песни',
//        'Юмор'
    ];

    protected function configure()
    {
        $this
            ->setName('parser:run')
            ->setDescription('Run parsing')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'path to folder with files to parse');
    }

    protected function hasChildrenDirs($path)
    {
        foreach (glob($path . '/*') as $filename) {
            if (is_dir($filename))
                return true;
        }

        return false;
    }

    protected function saveStructure($root, Category &$parentCategory = null, Author &$author = null)
    {
        foreach (glob($root . '/*') as $filename) {
            if (is_dir($filename)) {
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
        $path = $input->getOption('path');

        if (!$path)
            throw new \Exception('Path to folder with files is required');

        if (!file_exists($path))
            throw new \Exception('Path to folder with files does not exist');

        $this->root_dir = $path;

        $this->manager = $this->getManager();

        $this->manager->transactional(function () use ($path, $output) {
            $output->writeln('Parsing directory structure');
            $this->saveStructure($path);
            $output->writeln('Saving categories and authors');

            /** @var PDOConnection $con */
            $con = $this->getDoctrine()->getConnection()->getWrappedConnection();

            $aCmd = $con->prepare('insert into authors (id, name, slug, created, updated) values (nextval(\'authors_id_seq\'), :name, :slug, current_timestamp, current_timestamp)');
            $cCmd = $con->prepare('insert into categories (id, parent_id, name, slug, created, updated) values (nextval(\'authors_id_seq\'), (select id from categories where slug = :parent_slug limit 1), :name, :slug, current_timestamp, current_timestamp)');
            $bCmd = $con->prepare('insert into books (id, author_id, name, slug, filename, created, updated) values (nextval(\'authors_id_seq\'), (select id from authors where slug = :author_slug limit 1), :name, :slug, :filename, current_timestamp, current_timestamp)');
            $assocCmd = $con->prepare('insert into books_categories (book_id, category_id) values ((select id from books where slug = :book_slug limit 1), (select id from categories where slug = :category_slug limit 1))');

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

        while (strpos($phrase, '--') !== false)
            $phrase = str_replace('--', '-', $phrase);

        return $phrase;
    }

}