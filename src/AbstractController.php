<?php
namespace Gungnir\Framework;

use Gungnir\Core\ApplicationInterface;
use \Gungnir\HTTP\{Request, Response};

/**
 * Base class for controllers in framework
 *
 * @package gungnir-mvc\framework
 * @author Conny Karlsson <connykarlsson9@gmail.com>
 */
abstract class AbstractController implements ControllerInterface
{
    /** @var ApplicationInterface */
    private $application = null;

    /**
     * AbstractController constructor.
     *
     * @param ApplicationInterface $application
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * @inheritDoc
     */
    public function getApplication(): ApplicationInterface
    {
        return $this->application;
    }


    /**
     * @inheritDoc
     */
    public function before(Request $request) {}

    /**
     * @inheritDoc
     */
    public function after(Request $request, Response $response) {}

}