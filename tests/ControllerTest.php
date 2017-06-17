<?php
namespace Gungnir\Framework\Tests;

use Gungnir\Core\Application;
use \Gungnir\Framework\AbstractController;

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
        $controller = new TestableAbstractController(new Application());
        $this->assertInstanceOf(AbstractController::class, $controller);
    }
}

/**
 * Only purpose of this class is to extend abstract base controller so it can be tested.
 *
 * @package Gungnir\Framework\Tests
 */
class TestableAbstractController extends AbstractController {}
