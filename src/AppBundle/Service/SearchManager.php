<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/13/15
 * Time: 6:26 PM
 */

namespace AppBundle\Service;

use AppBundle\Entity\Author;
use Doctrine\ORM\Query;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchManager
{
    /**
     * @var ContainerInterface
     */
    protected $constainer;

    /**
     * @var \SphinxClient
     */
    protected $sphinx;

    public function __construct(ContainerInterface $container, \SphinxClient $sphinx)
    {
        $this->container = $container;
        $this->sphinx = $sphinx;
    }

    public function searchDocs($name)
    {
        $name = trim($name);

        $this->sphinx->setLimits(0, 20, 100);
        $this->sphinx->setMaxQueryTime(20);
//        $this->sphinx->setFieldWeights(array(
//            'name' => 10,
//            'value' => 10
//        ));
        $result = $this->sphinx->query($name . '*');

        if (!$result || !$result['total']) {
            return array();
        }

        $searchs = array();

        foreach ($result['matches'] as $match) {
            $pres = $this->sphinx->BuildExcerpts([$match['attrs']['name']], 'library', $name . '*', array(
                'limit' => 200,
                'exact_phrase' => false,
                //'before_match' => '',
                //'after_match' => '',
                'chunk_separator' => '',
                //'around' => 1
            ));

            $attrs = $match['attrs'];
            $docType = $attrs['doc_type'];

            $searchs[] = [
                'result' => $pres[0],
                'path' => ($docType === 'authors') ? $this->container->get('router')->generate('author_name', ['slug' => $attrs['slug']]) : $this->container->get('router')->generate('book', ['slug' => $attrs['slug']]),
                'doc_type' => $docType
            ];

        }

        return $searchs;
    }
}