<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/15/15
 * Time: 1:30 PM
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * AppBundle\Entity\Book
 *
 * @ORM\Table(name="books_pages",indexes={@Index(name="search_book_page", columns={"book_id", "page"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\BookPageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BookPage
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
     * @var Book $book
     *
     * @ORM\ManyToOne(targetEntity="Book", inversedBy="books_pages", cascade={"persist"})
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $book;

    /**
     * @var int $page
     *
     * @ORM\Column(name="page", type="integer", nullable=false)
     */
    private $page;

    /**
     * @var string $content
     *
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Book
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     * @param Book $book
     * @return BookPage
     */
    public function setBook($book)
    {
        $this->book = $book;
        return $this;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return BookPage
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return BookPage
     */
    public function setContent($content)
    {
        $this->content = $content;
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

}