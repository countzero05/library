<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * AppBundle\Entity\Book
 *
 * @ORM\Table(name="books",indexes={@Index(name="search_book_name", columns={"name", "author_id"}), @Index(name="search_book_slug", columns={"slug", "author_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BookRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Book
{

    use CUTrait;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Author $author
     *
     * @ORM\ManyToOne(targetEntity="Author", inversedBy="books", cascade={"persist"})
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $author;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $filename
     *
     * @ORM\Column(name="filename", type="string", nullable=false)
     */
    private $filename;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", unique=true, nullable=false)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="books", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="books_categories")
     **/
    private $books_categories;


    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="BookPage", mappedBy="book", cascade={"persist"})
     * @ORM\OrderBy({"page" = "ASC"})
     */
    private $books_pages;

    /**
     * @var int
     *
     * @ORM\Column(name="page_count", type="integer", nullable=true)
     */
    private $page_count;

    public function __construct()
    {
        $this->books_categories = new ArrayCollection();
        $this->books_pages = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Author
     */
    public function getAuthor(): Author
    {
        return $this->author;
    }

    /**
     * @param Author $author
     * @return $this
     */
    public function setAuthor(Author $author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBooksCategories()
    {
        return $this->books_categories;
    }

    /**
     * @param mixed $books_categories
     * @return $this
     */
    public function setBooksCategories($books_categories)
    {
        $this->books_categories = $books_categories;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getBooksPages(): Collection
    {
        return $this->books_pages;
    }

    /**
     * @param Collection $books_pages
     * @return $this
     */
    public function setBooksPages(Collection $books_pages)
    {
        $this->books_pages = $books_pages;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return (int)$this->page_count;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @return $this
     */
    public function setPageCount()
    {
        $this->page_count = count($this->books_pages);
        return $this;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category)
    {
        if (!$this->books_categories->contains($category)) {
            $this->books_categories->add($category);
        }

        return $this;
    }

}