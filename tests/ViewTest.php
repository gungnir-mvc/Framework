<?php
namespace Gungnir\Framework\Tests;

use org\bovigo\vfs\vfsStream;
use \Gungnir\Framework\{View, ViewException};

class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testItCanLoadViewFileFromPath()
    {
        $root = vfsStream::setup();
        $expectedViewContent = 'View Content';
        $rootPath = $root->url();
        file_put_contents($rootPath . '/foobar.php', $expectedViewContent);
        $view = new View($rootPath . '/foobar');

        $viewContent = $view->render();

        $this->assertEquals($expectedViewContent, $viewContent);
    }

    /**
     * @expectedException \Gungnir\Framework\ViewException
     */
    public function testItThrowsAnViewExceptionIfViewFileIsMissing()
    {
        $view = new View('foobar');
        $view->render();
    }

    public function testItCanLoadViewFileFromFallbackPath()
    {
        $root = vfsStream::setup();
        $applicationFolder = vfsStream::newDirectory('application/view');
        $root->addChild($applicationFolder);

        $expectedViewContent = 'View Content';
        file_put_contents($applicationFolder->url() . '/foobar.php', $expectedViewContent);

        $view = new View($root->url() . '/foobar', $applicationFolder->url() . '/');

        $viewContent = $view->render();

        $this->assertEquals($expectedViewContent, $viewContent);
    }


    public function testViewVariablesCanBeBoundAndUsed()
    {
        $root = vfsStream::setup();
        $applicationFolder = vfsStream::newDirectory('application/view');
        $root->addChild($applicationFolder);

        $expectedViewContent = '<?php echo $testVariable; ?>';
        file_put_contents($applicationFolder->url() . '/foobar.php', $expectedViewContent);

        $expected = 'Hello, World!';

        $view = new View($root->url() . '/foobar', $applicationFolder->url() . '/');
        $view->testVariable = $expected;

        $viewContent = $view->render();

        $this->assertEquals($expected, $viewContent);
    }

    public function testGlobalViewVariablesCanBeBoundAndUsed()
    {
        $root = vfsStream::setup();
        $applicationFolder = vfsStream::newDirectory('application/view');
        $root->addChild($applicationFolder);

        $expectedViewContent = '<?php echo $testVariable; ?>';
        file_put_contents($applicationFolder->url() . '/foobar.php', $expectedViewContent);

        $expected = 'Hello, World!';

        $view = new View($root->url() . '/foobar', $applicationFolder->url() . '/');

        View::setGlobal('testVariable', $expected);

        $viewContent = $view->render();

        $this->assertEquals($expected, $viewContent);
    }
}
