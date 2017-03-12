<?php

namespace AppBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;

class Menu extends \Twig_Extension
{
    /**
     * @var EntityRepository
     */
    private $repo;

    public function __construct(Registry $doctrine)
    {
        $this->repo = $doctrine->getRepository('AppBundle:Category');
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction ('menu', [$this, 'menu']),
        ];
    }

    public function menu()
    {
        return $this->repo->getRootDirectories();
    }

    public function getName()
    {
        return 'menu_widget';
    }
}
