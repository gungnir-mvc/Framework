<?php
namespace Gungnir\Framework;

use \Gungnir\Core\Application;
use Gungnir\Core\ApplicationInterface;
use Gungnir\Core\ContainerInterface;
use \Gungnir\HTTP\{Request,Response,Route,HttpException};
use \Gungnir\Event\{EventDispatcher, GenericEventObject};

class Dispatcher implements DispatcherInterface
{
    const CONTAINER_KERNEL_NAME           = 'Application';
    const CONTAINER_EVENT_DISPATCHER_NAME = 'EventDispatcher';

    /** @var Application */
    private $application = null;

    /**
     * Dispatcher constructor.
     *
     * @param ApplicationInterface $application
     */
    public function __construct(ApplicationInterface $application)
    {
        $this->application = $application;
    }

    /**
     * Get the EventDispatcher instance from registered container
     * or pulls it from the application level and injects one into the container and
     * returns that instance.
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        if ($this->getContainer()->has(self::CONTAINER_EVENT_DISPATCHER_NAME)) {
            return $this->getContainer()->get(self::CONTAINER_EVENT_DISPATCHER_NAME);
        }

        $this->getContainer()->store(
            self::CONTAINER_EVENT_DISPATCHER_NAME,
                $this->application->getEventDispatcher()
        );
        return $this->getEventDispatcher();
    }

    /**
     * {@inheritdoc}
     */
    public function getContainer(): ContainerInterface
    {
        return $this->application->getContainer();
    }

    /**
     * @inheritDoc
     */
    public function dispatch(Request $request): Response
    {
        // Fetch and store route
        $uri   = $this->getContainer()->has('uri') ? $this->getContainer()->get('uri') : null;
        $this->getEventDispatcher()->emit('gungnir.http.dispatcher.locate.route.uri', new GenericEventObject($uri));
        $route = $this->getRoute($uri);
        $this->getContainer()->store('route', $route);

        $this->getEventDispatcher()->emit('gungnir.http.dispatcher.located.route', new GenericEventObject($this->application));

        // If route have any parameters then re-initialize the request with existing data
        if (empty($route->parameters()) !== true) {
            $request->initialize(
                $request->query()->parameters(),
                $request->request()->parameters(),
                $route->parameters(),
                $request->cookies()->parameters(),
                $request->files()->parameters(),
                $request->server()->parameters()
            );
        }

        $this->getContainer()->store('request', $request);
        $this->getEventDispatcher()->emit('gungnir.http.dispatcher.located.request', new GenericEventObject($this->application));

        // Fetch and store controller
        $controller = $this->getController($route);
        $this->getContainer()->store('controller', $controller);
        $this->getEventDispatcher()->emit('gungnir.http.dispatcher.located.controller', new GenericEventObject($this->application));

        // Fetch and store action
        $action = $this->getAction($request, $route);
        $this->getContainer()->store('action', $action);
        $this->getEventDispatcher()->emit('gungnir.http.dispatcher.located.action', new GenericEventObject($this->application));

        // Execute controller with action
        $response = $this->runController(
            $this->getContainer()->get('controller'),
            $this->getContainer()->get('action')
        );

        $this->getEventDispatcher()->emit('gungnir.http.dispatcher.located.response', new GenericEventObject($this->application));

        return $response;
    }

    /**
     * Identifies and retrieves a route object based
     * on an incoming URL.
     *
     * @param  String $uri URL that will be parsed
     *
     * @throws HttpException
     *
     * @return Route
     */
    public function getRoute(String $uri = null) : Route
    {

        $uri = $uri ?? $_SERVER['REQUEST_URI'];

        $route = Route::find($uri);

        if (empty($route)) {
            throw new HttpException('No matching route was found for: ' . $uri);
        }

        return $route;
    }

    /**
     * Builds up a request object based on a given Route
     * and then returns it.
     *
     * @param  Route  $route The incoming route
     * 
     * @return \Gungnir\HTTP\Request
     */
    public function getRequest(Route $route) : Request
    {
        $request = new Request($_GET, $_POST, $route->parameters(), $_COOKIE, $_FILES, $_SERVER);
        return $request;
    }

    /**
     * Retrieves action name and modifies to be a valid callable
     * action name.
     *
     * @param  \Gungnir\HTTP\Request  $request Incoming request
     * @param  \Gungnir\HTTP\Route    $route   Incoming route
     *
     * @return String            Valid action name to be called inside controller
     */
    public function getAction(Request $request, Route $route) : String
    {
        $method = $request->server()->get('REQUEST_METHOD');

        if (empty($method)) {
            $method = $_SERVER['REQUEST_METHOD'];
        }

        $action = strtolower($method) . $route->action();
        return ($route->isActionValid($action)) ? $action : '' ;
    }

    /**
     * Creates a controller object and stores it inside
     * the application container
     *
     * @param Route $route
     *
     * @return ControllerInterface
     * @throws HttpException
     */
    private function getController(Route $route)
    {
        $controller = $route->controller();
        $eventName  = 'gungnir.http.dispatcher.locate.controller_name';

        $this->getEventDispatcher()->emit($eventName, new GenericEventObject([
            'dispatcher' => $this,
            'controller' => $controller
        ]));

        $this->getContainer()->store('controller_name', $controller);

        if (class_exists($controller)) {

            /** @var ControllerInterface $controller */
            $controller = new $controller($this->application);

            $eventName = 'gungnir.http.dispatcher.locate.controller';

            $this->getEventDispatcher()->emit($eventName, new GenericEventObject([
                'dispatcher' => $this,
                'controller' => $controller
            ]));

        } else {
            throw new HttpException('Controller '.$controller.' does not exist.');
        }
        return $controller;
    }

    /**
     * Executes action for controller for current request
     *
     * @param ControllerInterface $controller
     * @param string $action
     *
     * @return Response
     * @throws HttpException
     */
    private function runController(ControllerInterface $controller, string $action) : Response
    {
        $request    = $this->getContainer()->get('request');

        $response   = $controller->before($request);

        if (empty($response)) {
            if (is_callable([$controller, $action])) {
                $response = call_user_func_array([$controller, $action], [$request]);
            } else {
                throw new HttpException(sprintf('Action %s does not exist.', $action));
            }
        }

        $this->getContainer()->store('response', $response);

        $controller->after($request, $response);

        return $response;
    }
}
