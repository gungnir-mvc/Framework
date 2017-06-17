<?php
namespace Gungnir\Framework;

use Gungnir\Core\ContainerInterface;
use Gungnir\HTTP\Request;
use Gungnir\HTTP\Response;

interface DispatcherInterface
{

    /**
     * Get the container from dispatcher
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface;

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