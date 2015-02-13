<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/13/15
 * Time: 6:26 PM
 */

namespace AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchFactory
{
    /** @var SearchManager $manager */
    protected static $manager = null;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \SphinxClient
     */
    protected $sphinx;

    public function __construct($container, $host, $port, $collection)
    {
        $this->container = $container;

        $this->sphinx = new \SphinxClient();
        $this->sphinx->SetServer($host, $port);

        //Совпадение любого из слов
        $this->sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);

        //Результаты отсортированы по релевантности
        $this->sphinx->SetSortMode(SPH_RANK_PROXIMITY_BM25);

        //Задаем полям веса, для подсчета релевантности
        //$w = array ('key' => 10, 'value' => 10);
        //$sphinx->SetFieldWeights($w);
        $this->sphinx->setLimits(0, 20, 100);
        //$sphinx->setArrayResult(true);

    }

    public function get()
    {
        if (self::$manager === null) {
            self::$manager = new SearchManager($this->container, $this->sphinx);
        }

        return self::$manager;
    }

}