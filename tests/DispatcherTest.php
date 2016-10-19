<?php
namespace Gungnir\Framework\Tests;

use \Gungnir\Framework\Dispatcher;
use \Gungnir\Core\Container;
use \Gungnir\HTTP\Route;
use \org\bovigo\vfs\vfsStream;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $root = null;

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
            'defaults' => [
                'controller' => 'index',
                'action' => 'index'
            ]
        ]);

        Route::add('testRoute', $route);
    }

    public function testItCanBeRun()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $container = new Container;
        $container->store('uri', '/testRoute');
        $dispatcher = new Dispatcher($container);
        $result = $dispatcher->run();
        $this->assertInstanceOf(Dispatcher::class, $dispatcher);
    }

    private function getTestControllerFileContent()
    {
        $contentString  = "<?php" . PHP_EOL;
        $contentString .= "namespace Gungnir\Test\Controller;" . PHP_EOL;
        $contentString .= "use \Gungnir\Framework\Controller;" . PHP_EOL;
        $contentString .= "use \Gungnir\HTTP\Response;" . PHP_EOL;
        $contentString .= "class Index extends Controller { public function getIndex(){ return new Response(); } }" . PHP_EOL;
        return $contentString;
    }
}
