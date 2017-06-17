<?php
namespace Gungnir\Framework\Tests;

use Gungnir\Core\Application;
use \Gungnir\Framework\Dispatcher;
use \Gungnir\Core\Container;
use Gungnir\HTTP\HttpException;
use Gungnir\HTTP\Request;
use Gungnir\HTTP\Response;
use \Gungnir\HTTP\Route;
use \org\bovigo\vfs\vfsStream;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $root = null;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $structure = [
            'application' => [
                'classes' => [
                    'Gungnir' => [
                        'Test' => [
                            'Controller' => []
                        ]
                    ]
                ]
            ]
        ];

        $this->root = vfsStream::setup();
        vfsStream::create($structure);
        $controllerFolder = $this->root->getChild('application/classes/Gungnir/Test/Controller');
        $controller = vfsStream::newFile('Index.php');
        $controller->rename('Index.php');
        $controller->setContent($this->getTestControllerFileContent());
        $controllerFolder->addChild($controller);

        $autoloader = new \Gungnir\Core\Autoloader($this->root->url());

        spl_autoload_register([$autoloader, 'classLoader']);

        $route = new Route('/testRoute/:controller/:action', [
            'namespace' => '\Gungnir\Test\Controller\\',
            'actions' => ['getIndex'],
            'defaults' => [
                'controller' => 'index',
                'action' => 'index'
            ]
        ]);

        Route::add('testRoute', $route);
    }

    /**
     * @test
     */
    public function dispatcherReturnsAnResponseWhenRun()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = new Container;
        $container->store('uri', '/testRoute/index/index');
        $application = new Application();
        $dispatcher = new Dispatcher($application);
        $dispatcher->setContainer($container);
        $request = new Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
        $result = $dispatcher->dispatch($request);

        $this->assertInstanceOf(Response::class, $result);
    }

    /**
     * @test
     * @expectedException \Gungnir\HTTP\HttpException
     */
    public function itValidatesRoutingAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = new Container;
        $container->store('uri', '/testRoute/index/internalAsset');
        $application = new Application();
        $dispatcher = new Dispatcher($application);
        $dispatcher->setContainer($container);
        $request = new Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
        $dispatcher->dispatch($request);
    }

    /**
     * @return string
     */
    private function getTestControllerFileContent()
    {
        $contentString  = "<?php" . PHP_EOL;
        $contentString .= "namespace Gungnir\Test\Controller;" . PHP_EOL;
        $contentString .= "use \Gungnir\Framework\AbstractController;" . PHP_EOL;
        $contentString .= "use \Gungnir\HTTP\Response;" . PHP_EOL;
        $contentString .= "class Index extends AbstractController { public function getIndex(){ return new Response(); }}" . PHP_EOL;
        return $contentString;
    }
}
