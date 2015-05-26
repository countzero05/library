<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Book
 *
 * @ORM\Table(name="authors")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\BookRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Author
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=false, unique=true)
     */
    private $name;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", unique=true, nullable=false)
     */
    private $slug;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Book", mappedBy="author")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $books;

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

    /**
     * @var \DateTime $biography_updated
     *
     * @ORM\Column(name="biography_updated", type="datetime", nullable=true)
     */
    private $biography_updated;

    /**
     * @var \string $biography
     *
     * @ORM\Column(name="biography", type="text", nullable=true)
     */
    private $biography;

    public function __construct()
    {
        $this->books = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getBooks()
    {
        return $this->books;
    }

    /**
     * @param ArrayCollection $books
     * @return Author
     */
    public function setBooks($books)
    {
        $this->books = $books;
        return $this;
    }

    public function __toString()
    {
        return $this->slug;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        $categories = [];

        /** @var Book $book */
        foreach ($this->books->getIterator() as $book) {
            $categories = array_merge($categories, $book->getBooksCategories()->toArray());
        }

        return array_unique($categories);
    }

    /**
     * @return \DateTime
     */
    public function getBiographyUpdated()
    {
        return $this->biography_updated;
    }

    /**
     * @param \DateTime $biography_updated
     * @return Author
     */
    public function setBiographyUpdated($biography_updated)
    {
        $this->biography_updated = $biography_updated;
        return $this;
    }

    /**
     * @return string
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * @param string $biography
     * @return Author
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;
        return $this;
    }

}