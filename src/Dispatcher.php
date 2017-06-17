<?php
namespace Gungnir\Framework;

use \Gungnir\Core\Kernel;
use \Gungnir\Core\Container;
use \Gungnir\HTTP\{Request,Response,Route,HttpException};
use \Gungnir\Event\{EventDispatcher, GenericEventObject};

class Dispatcher implements DispatcherInterface
{
    const CONTAINER_KERNEL_NAME           = 'Application';
    const CONTAINER_EVENT_DISPATCHER_NAME = 'EventDispatcher';

    /** @var Kernel */
    private $application = null;

    /** @var Container **/
    private $container = null;

    /**
     * Dispatcher constructor.
     *
     * @param Kernel $application
     */
    public function __construct(Kernel $application)
    {
        $this->application = $application;
    }

    /**
     * Get the EventDispatcher instance from registered container
     * or creates and injects one into the container and
     * returns the new instance
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        if ($this->getContainer()->has(self::CONTAINER_EVENT_DISPATCHER_NAME)) {
            return $this->getContainer()->get(self::CONTAINER_EVENT_DISPATCHER_NAME);
        }

        $eventDispatcher = new EventDispatcher;
        $this->getContainer()->store(self::CONTAINER_EVENT_DISPATCHER_NAME, $eventDispatcher);
        return $this->getEventDispatcher();
    }

    /**
     * Get the container from dispatcher
     *
     * @return Container
     */
    public function getContainer()
    {
        if (empty($this->container)) {
            $this->container = new Container();
        }
        return $this->container;
    }

    /**
     * Set container to dispatcher
     *
     * @return Dispatcher
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function dispatch(Request $request): Response
    {
        $this->loadApplicationEventListeners();
        $this->getContainer()->store('request', $request);
        $this->locateRoute();

        // Inject routing parameters to the request
        $this->getContainer()
            ->get('request')
            ->parameters(
                $this->getContainer()
                    ->get('route')
                    ->parameters());

        $this->locateController();
        $this->locateAction();
        return $this->runController();
    }

    /**
     * Identifies and retrieves a route object based
     * on an incoming URL.
     *
     * @param  String $uri URL that will be parsed
     * 
     * @return \Gungnir\HTTP\Route Route found
     */
    public function getRoute(String $uri = null) : Route
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'];
        return Route::find($uri);
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
     * Loads event listeners into event dispatcher from
     * application configuration scope
     * 
     * @return void
     */
    private function loadApplicationEventListeners()
    {
        $appRoot        = $this->application->getApplicationPath();
        $file           = $appRoot . 'config/EventListeners.php';
        $eventListeners = file_exists($file) ? require $file : [];

        if (empty($eventListeners) !== true && is_array($eventListeners)) {
            $this->getEventDispatcher()->registerListeners($eventListeners);
        }

        $eventName = 'gungnir.framework.loadapplicationeventlisteners.done';
        $this->getEventDispatcher()->emit($eventName, new GenericEventObject($this));
    }

    /**
     * Creates a controller object and stores it inside
     * the application container
     *
     * @throws HttpException
     * @return void
     */
    private function locateController()
    {
        $controller = $this->getContainer()->get('route')->controller();
        $eventName  = 'gungnir.http.dispatcher.locatecontroller.name';

        $this->getEventDispatcher()->emit($eventName, new GenericEventObject([
            'dispatcher' => $this,
            'controller' => $controller
        ]));
        $this->getContainer()->store('controller_name', $controller);

        if (class_exists($controller)) {

            /** @var ControllerInterface $controller */
            $controller = new $controller;

            $eventName = 'gungnir.http.dispatcher.locatecontroller.object';

            $this->getEventDispatcher()->emit($eventName, new GenericEventObject([
                'dispatcher' => $this,
                'controller' => $controller
            ]));
            $this->getContainer()->store('controller', $controller);

        } else {
            throw new HttpException('Controller '.$controller.' does not exist.');
        }
    }

    /**
     * Locates route and stores it inside application container
     *
     * @throws HttpException
     *
     * @return void
     */
    private function locateRoute()
    {
        $uri       = $this->getContainer()->has('uri') ? $this->getContainer()->get('uri') : null;
        $eventName = 'gungnir.http.dispatcher.locateroute.uri';

        $this->getEventDispatcher()->emit($eventName, new GenericEventObject($uri));

        $route = $this->getRoute($uri);

        if (empty($route)) {
            throw new HttpException('No matching route was found for: ' . $uri);
        }

        $eventName = 'gungnir.http.dispatcher.locateroute.route';

        $this->getEventDispatcher()->emit($eventName, new GenericEventObject($route));
        $this->getContainer()->store('route', $route);
    }

    /**
     * Locates and stores action to be called inside application container
     *
     * @return void
     */
    private function locateAction()
    {
        $action    = $this->getAction($this->getContainer()->get('request'), $this->getContainer()->get('route'));
        $eventName = 'gungnir.http.dispatcher.locateaction.action';
        
        $this->getEventDispatcher()->emit($eventName, new GenericEventObject($action));
        $this->getContainer()->store('action', $action);
    }

    /**
     * Executes action for controller for current request
     *
     * @throws HttpException
     * 
     * @return \Gungnir\HTTP\Response
     */
    private function runController() : Response
    {
        $controller = $this->getContainer()->get('controller');
        $action     = $this->getContainer()->get('action');
        $request    = $this->getContainer()->get('request');
        $response   = $controller->before($request);

        $eventName  = 'gungnir.framework.dispatcher.runcontroller.before.response';
        $this->getEventDispatcher()->emit($eventName, new GenericEventObject($response));

        if (empty($response)) {
            if (is_callable([$controller, $action])) {
                $response = call_user_func_array([$controller, $action], [$request]);
            } else {
                throw new HttpException('Action '.$action.' does not exist.');
            }
        }

        $eventName = 'gungnir.framework.dispatcher.runcontroller.action.response';

        $this->getEventDispatcher()->emit($eventName, new GenericEventObject($response));

        $this->getContainer()->store('response', $response);

        $controller->after($request, $response);

        return $response;
    }
}
