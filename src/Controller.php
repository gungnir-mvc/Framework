<?php
namespace Gungnir\Framework;

use \Gungnir\Core\Container;

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
     * @return void
     */
    public function before() {}

    /**
     * Method that runs after action is invoked
     *
     * @return void
     */
    public function after() {}

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
