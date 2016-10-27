<?php
namespace Gungnir\Framework;

use \Gungnir\Core\Container;
use \Gungnir\HTTP\{Request, Response};

/**
 * @package gungnir-mvc\framework
 * @author Conny Karlsson <connykarlsson9@gmail.com>
 */
class Controller
{
    /** @var Container */
    private $container = null;

    /**
     * Method that runs before action is invoked.
     *
     * @param \Gungnir\HTTP\Request $request The incoming request object
     * 
     * @return void|\Gungnir\HTTP\Response
     */
    public function before(Request $request) {}

    /**
     * Method that runs after action is invoked
     *
     * @param \Gungnir\HTTP\Request  $request  The incoming request object
     * @param \Gungnir\HTTP\Response $response The generated response object
     * 
     * @return void
     */
    public function after(Request $request, Response $response) {}

    /**
     * Set container on controller
     *
     * @param Container $container The container to bind to the controller
     *
     * @return Controller
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Get container for controller
     *
     * @return Container|Null
     */
    public function getContainer()
    {
        return $this->container;
    }
}