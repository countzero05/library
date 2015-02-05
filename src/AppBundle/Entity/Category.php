<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * AppBundle\Entity\Category
 *
 * @ORM\Table(name="categories",indexes={@Index(name="search_category_name", columns={"name"}),@Index(name="search_category_slug", columns={"slug"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Category
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
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", nullable=false)
     */
    private $slug;

    /**
     * @var Category $parent
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="categories", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity="Book", mappedBy="books_categories", cascade={"persist", "remove"})
     **/
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

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
     * @return Category
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Category $parent
     * @return Category
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param ArrayCollection $categories
     * @return Category
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
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
     * @return Category
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
     * @return Category
     */
    public function setBooks($books)
    {
        $this->books = $books;
        return $this;
    }

    public function addBook(Book $book)
    {
        if (!$this->books->contains($book))
            $this->books->add($book);
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Author[]
     */
    public function getAuthors()
    {
        return array_unique($this->getBooks()->map(function (Book $book) {
            return $book->getAuthor();
        })->toArray());
    }
}