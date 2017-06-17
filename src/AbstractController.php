<?php
namespace Gungnir\Framework;

use \Gungnir\HTTP\{Request, Response};

/**
 * Base class for controllers in framework
 *
 * @package gungnir-mvc\framework
 * @author Conny Karlsson <connykarlsson9@gmail.com>
 */
abstract class AbstractController implements ControllerInterface
{

    /**
     * @inheritDoc
     */
    public function before(Request $request) {}

    /**
     * @inheritDoc
     */
    public function after(Request $request, Response $response) {}

}