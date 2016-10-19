<?php
namespace Gungnir\Framework\Tests;

use \Gungnir\Framework\Model;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testModelCanValidateItselfAgainstRequirements()
    {
        $model = new Model;
        $model->addRequirement('test_attribute');

        $this->assertFalse($model->validate());

        $model->test_attribute = true;

        $this->assertTrue($model->validate());
    }
}
