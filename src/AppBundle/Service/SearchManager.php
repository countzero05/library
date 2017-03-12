<?php

namespace AppBundle\Service;

use IAkumaI\SphinxsearchBundle\Search\Sphinxsearch;
use Symfony\Component\Routing\Router;
require_once __DIR__ . "/../../../vendor/iakumai/sphinxsearch-bundle/IAkumaI/SphinxsearchBundle/Sphinx/SphinxAPI.php";

class SearchManager
{

    /**
     * @var Router
     */
    private $router;
    /**
     * @var Sphinxsearch
     */
    private $sphinx;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $port;
    /**
     * @var string
     */
    private $indexName;

    /**
     * SearchManager constructor.
     * @param Router $router
     * @param string $host
     * @param string $port
     * @param string $indexName
     */
    public function __construct(Router $router, string $host, string $port, string $indexName)
    {
        $this->router = $router;

        $this->host = $host;
        $this->port = $port;
        $this->indexName = $indexName;

        $this->sphinx = new Sphinxsearch($this->host, $this->port);
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
        $result = $this->sphinx->search($name . '*', [$this->indexName]);

        if (!$result || !$result['total']) {
            return array();
        }

        $searchs = array();

        foreach ($result['matches'] as $match) {
            $pres = $this->sphinx->BuildExcerpts([$match['attrs']['name']], $this->indexName, $name . '*', array(
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
                'path' => ($docType === 'authors') ? $this->router->generate('author_name', ['slug' => $attrs['slug']]) : $this->router->generate('book', ['slug' => $attrs['slug']]),
                'doc_type' => $docType
            ];

        }

        return $searchs;
    }
}