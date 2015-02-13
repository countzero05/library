<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2/13/15
 * Time: 9:42 PM
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Search controller.
 *
 * @Cache(expires="-1 minute", public="true", smaxage="60")
 * @Route("/")
 */
class SearchController extends DefaultController
{

    /**
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request)
    {
        $results = $this->get('search_manager')->searchDocs($request->query->get('q', ''));
        $response = new JsonResponse();
        $response->setData($results);

        return $response;
    }
}