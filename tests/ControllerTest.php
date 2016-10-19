<?php
namespace Gungnir\Framework\Tests;

use \Gungnir\Framework\Controller;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Very basic test that at least screams if constructor
     * gets changed.
     *
     * Controllers are what the developer makes them and therefore
     * does not require a lot of testing
     *
     * @test
     */
    public function testItCanBeInstantiated()
    {
        $controller = new Controller;
        $this->assertInstanceOf(Controller::class, $controller);
    }
}
