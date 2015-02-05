<?php
namespace AppBundle\Twig\Extension;

class Menu extends \Twig_Extension
{
    /**
     * @var \Twig_Environment $environment
     */
    protected $environment;

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getFunctions()
    {
        return array(
            'menu' => new \Twig_Function_Method($this, 'menu'),
        );
    }

    public function menu()
    {
        $categories = $this->container->get('doctrine')->getRepository('AppBundle:Category')->createQueryBuilder('c')->where('c.parent is null')->orderBy('c.name', 'ASC')->getQuery()->getResult();

        return $this->container->get('templating')->render('AppBundle:Extensions:menu.html.twig', [
            'categories' => $categories,
        ]);
    }

    public function getName()
    {
        return 'menu_widget';
    }
}
