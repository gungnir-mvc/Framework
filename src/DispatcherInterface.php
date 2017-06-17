<?php
namespace Gungnir\Framework;

use Gungnir\HTTP\Request;
use Gungnir\HTTP\Response;

interface DispatcherInterface
{
    /**
     * Dispatches a request through application and returns an
     * response
     *
     * @param Request $request
     *
     * @return Response
     */
    public function dispatch(Request $request): Response;
}