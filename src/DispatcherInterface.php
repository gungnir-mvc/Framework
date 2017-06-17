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
     * Set the container to dispatcher
     *
     * @param ContainerInterface $container
     * @return DispatcherInterface
     */
    public function setContainer(ContainerInterface $container): DispatcherInterface;

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