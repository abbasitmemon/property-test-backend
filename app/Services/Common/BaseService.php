<?php


namespace App\Services\Common;

use App\Http\Controllers\Controller;
use App\Services\JsonResponseService;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseService
{
    /**
     * @var \App\Services\JsonResponseService
     */
    protected $response;

    /**
     * number of pagination
     * @var int
     */
    protected $pagination;

    public function __construct()
    {
        $this->response = new JsonResponseService;
        $requestPerPage = request('per_page', config('app.pagination'));
        $this->pagination = $requestPerPage > 100 ? 100 : $requestPerPage;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke()
    {
        return $this->response->fail([], 400, Response::HTTP_NOT_FOUND);
    }

}
