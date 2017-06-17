<?php
namespace Gungnir\Framework;

use \Gungnir\Core\Container;
use \Gungnir\HTTP\{Request, Response};

/**
 * @package gungnir-mvc\framework
 * @author Conny Karlsson <connykarlsson9@gmail.com>
 */
abstract class Controller implements ControllerInterface
{
    /** @var Container */
    private $container = null;

    /**
     * @inheritDoc
     */
    public function before(Request $request) {}

    /**
     * @inheritDoc
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