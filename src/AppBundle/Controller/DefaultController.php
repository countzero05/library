<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @return \AppBundle\Entity\CategoryRepository
     */
    protected function getCategoryRepository()
    {
        return $this->get('doctrine')->getRepository('AppBundle:Category');
    }

    /**
     * @return \AppBundle\Entity\AuthorRepository
     */
    protected function getAuthorRepository()
    {
        return $this->get('doctrine')->getRepository('AppBundle:Author');
    }

    /**
     * @return \AppBundle\Entity\BookRepository
     */
    protected function getBookRepository()
    {
        return $this->get('doctrine')->getRepository('AppBundle:Book');
    }

    /**
     * @return \AppBundle\Entity\BookPageRepository
     */
    protected function getBookPageRepository()
    {
        return $this->get('doctrine')->getRepository('AppBundle:BookPage');
    }

}
