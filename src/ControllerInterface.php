<?php
namespace Gungnir\Framework;

use Gungnir\HTTP\Request;
use Gungnir\HTTP\Response;

interface ControllerInterface
{
    /**
     * Method that runs before action is invoked. If a response is returned from
     * the before method. Primary action will note be invoked.
     *
     * @param Request $request The incoming request object
     *
     * @return void|Response
     */
    public function before(Request $request);

    /**
     * Method that runs after action is invoked
     *
     * @param Request  $request  The incoming request object
     * @param Response $response The generated response object
     *
     * @return void
     */
    public function after(Request $request, Response $response);
}