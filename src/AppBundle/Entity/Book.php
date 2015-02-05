<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * AppBundle\Entity\Book
 *
 * @ORM\Table(name="books",indexes={@Index(name="search_book_name", columns={"name", "author_id"}), @Index(name="search_book_slug", columns={"slug", "author_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\BookRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Book
{

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
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    public function __construct()
    {
        $this->books_categories = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Book
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @ORM\PrePersist()
     * @return Book
     */
    public function setCreated()
    {
        $this->created = $this->updated = new \DateTime();
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @ORM\PreUpdate()
     * @return Book
     */
    public function setUpdated()
    {
        $this->updated = new \DateTime();
        return $this;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return Book
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return Book
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return Author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param Author $author
     * @return Book
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    public function addCategory(Category $category)
    {
        if (!$this->books_categories->contains($category))
            $this->books_categories->add($category);
    }

    /**
     * @return ArrayCollection
     */
    public function getBooksCategories()
    {
        return $this->books_categories;
    }

    /**
     * @param mixed $books_categories
     * @return Book
     */
    public function setBooksCategories($books_categories)
    {
        $this->books_categories = $books_categories;
        return $this;
    }

}