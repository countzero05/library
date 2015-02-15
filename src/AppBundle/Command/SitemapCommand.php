<?php

namespace AppBundle\Command;

use AppBundle\Entity\Author;
use AppBundle\Entity\Book;
use AppBundle\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapCommand extends ContainerAwareCommand
{

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

    protected function configure()
    {
        $this
            ->setName('sitemap:generate')
            ->setDescription('Generate sitemap')
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'domain name of site required');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hostname = $input->getOption('domain');

        if (!$hostname) {
            throw new \Exception('Domain name of site required');
        }

        $em = $this->getManager();

        $urls = array();

        $router = $this->getContainer()->get('router');

        // add some urls homepage
        $urls[] = array('loc' => $router->generate('homepage'), 'changefreq' => 'weekly', 'priority' => '1.0');

        $urls[] = array('loc' => $router->generate('category'), 'changefreq' => 'weekly', 'priority' => '1.0');
        $urls[] = array('loc' => $router->generate('author'), 'changefreq' => 'weekly', 'priority' => '1.0');

        /** @var \stdClass[] $categories */
        $categories = $em->getRepository('AppBundle:Category')->createQueryBuilder('c')->select('c.slug slug')->getQuery()->getScalarResult();

        foreach ($categories as $category) {
            $urls[] = array('loc' => $router->generate('category_name', ['slug' => $category['slug']]), 'changefreq' => 'weekly', 'priority' => '1.0');
        }

        /** @var \stdClass[] $authors */
        $authors = $em->getRepository('AppBundle:Author')->createQueryBuilder('a')->select('a.slug slug')->getQuery()->getScalarResult();

        foreach ($authors as $author) {
            $urls[] = array('loc' => $router->generate('author_name', ['slug' => $author['slug']]), 'changefreq' => 'weekly', 'priority' => '1.0');
        }

        /** @var \stdClass[] $books */
        $books = $em->getRepository('AppBundle:Book')->createQueryBuilder('b')->select('b.slug slug')->getQuery()->getScalarResult();

        foreach ($books as $book) {
            $urls[] = array('loc' => $router->generate('book', ['slug' => $book['slug']]), 'changefreq' => 'weekly', 'priority' => '1.0');
        }

        $sitemapCount = ceil(count($urls) / 30000);

        $templating = $this->getContainer()->get('templating');

        $s = $templating->render('TwigBundle/views/Sitemap/index.xml.twig', ['sitemapCount' => $sitemapCount, 'hostname' => $hostname]);
        file_put_contents($this->getContainer()->getParameter('kernel.root_dir') . '/../web/sitemap.xml', $s);

        $i = 0;

        while (count($urls)) {
            $arr = array_splice($urls, 0, 30000);

            $s = $templating->render('TwigBundle/views/Sitemap/sitemap.xml.twig', ['urls' => $arr, 'hostname' => $hostname]);
            file_put_contents($this->getContainer()->getParameter('kernel.root_dir') . '/../web/sitemap' . ++$i . '.xml', $s);
        }

        $output->writeln('Sitemap successfully created');
    }

}