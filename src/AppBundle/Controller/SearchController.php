<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Search controller.
 *
 * @Cache(expires="-1 minute", public="true", smaxage="60")
 * @Route("/")
 */
class SearchController extends Controller
{

    /**
     * @Route("/search", name="search")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function searchAction(Request $request)
    {
        $results = $this->get('search_manager')->searchDocs($request->query->get('q', ''));
        $response = new JsonResponse();
        $response->setData($results);

        return $response;
    }
}