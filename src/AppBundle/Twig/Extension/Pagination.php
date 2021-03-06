<?php

namespace AppBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Pagination extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction ('pagination', [$this, 'pagination']),
            new \Twig_SimpleFunction ('author_pagination', [$this, 'authorPagination']),
            new \Twig_SimpleFunction ('author_letter_pagination', [$this, 'authorLetterPagination'])
        ];
    }

    public function pagination($page, $rowCount, $rowsPerPage = 60, $pagesPerPagination = 11, $extra)
    {
        $half = ceil(($pagesPerPagination - 1) / 2);

        $pageCount = ceil($rowCount / $rowsPerPage);

        $pageCount = $pageCount ? $pageCount : 1;

        $first = $page - $half;
        $last = $page + $half;

        if ($pagesPerPagination >= $pageCount) {
            $pages = range(1, $pageCount, 1);
            $prev = null;
            $next = null;
        } else {
            if ($first >= 1 && $last <= $pageCount) {
                $pages = range($first, $last, 1);
            } else if ($first < 1) {
                $pages = range(1, $pagesPerPagination, 1);
            } else {
                $pages = range($pageCount - $pagesPerPagination, $pageCount, 1);
            }

            $prev = $first;

            if ($prev <= 1)
                $prev = null;

            $next = $last;

            if ($next >= $pageCount)
                $next = null;

        }

        return $this->container->get('templating')->render('AppBundle:Extensions:pagination.html.twig', [
            'page' => $page,
            'pages' => $pages,
            'pageCount' => $pageCount,
            'prev' => $prev,
            'next' => $next,
            'extra' => $extra
        ]);
    }

    public function authorPagination($page, $rowCount, $rowsPerPage = 60, $pagesPerPagination = 11)
    {
        $half = ceil(($pagesPerPagination - 1) / 2);

        $pageCount = ceil($rowCount / $rowsPerPage);

        $pageCount = $pageCount ? $pageCount : 1;

        $first = $page - $half;
        $last = $page + $half;

        if ($pagesPerPagination >= $pageCount) {
            $pages = range(1, $pageCount, 1);
            $prev = null;
            $next = null;
        } else {
            if ($first >= 1 && $last <= $pageCount) {
                $pages = range($first, $last, 1);
            } else if ($first < 1) {
                $pages = range(1, $pagesPerPagination, 1);
            } else {
                $pages = range($pageCount - $pagesPerPagination, $pageCount, 1);
            }

            $prev = $first;

            if ($prev <= 1)
                $prev = null;

            $next = $last;

            if ($next >= $pageCount)
                $next = null;

        }

        return $this->container->get('templating')->render('AppBundle:Extensions:authorPagination.html.twig', [
            'page' => $page,
            'pages' => $pages,
            'pageCount' => $pageCount,
            'prev' => $prev,
            'next' => $next
        ]);
    }

    public function authorLetterPagination($page, $pages)
    {
        $half = ceil((count($pages) - 1) / 2);

        $pageCount = count($pages);

        $pageCount = $pageCount ? $pageCount : 1;

        $prev = null;
        $next = null;

        return $this->container->get('templating')->render('AppBundle:Extensions:authorLetterPagination.html.twig', [
            'page' => $page,
            'pages' => $pages,
            'pageCount' => $pageCount,
            'prev' => $prev,
            'next' => $next
        ]);
    }

    public function getName()
    {
        return 'pagination_widget';
    }
}
