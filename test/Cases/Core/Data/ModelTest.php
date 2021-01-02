<?php

use PHPUnit\Framework\TestCase;
use PHPSimpleLib\Core\Data\Model;

class TestModel extends Model
{

}

final class ModelTest extends TestCase
{
    /**
     * Model object for testing
     *
     * @var \PHPSimpleLib\Core\Data\Model
     */
    protected $model = null;

    protected function setUp() : void
    {
        $this->model = new TestModel();
    }

    public function testClass()
    {
        $this->assertInstanceOf(
            Model::class,
            $this->model
        );
    }

    public function testEmptyModel()
    {
        $this->assertTrue(count($this->model->getDirtyFields()) === 0);
    }

    public function testMissingField()
    {
        $this->assertFalse($this->model->hasExistingField('nonExistingField'));
    }

    public function testDirtyIndicatorField()
    {
        $this->assertFalse($this->model->isDirty());
    }

    public function testDirtyIndicatorFieldWithData()
    {
        $this->model->newField = true;
        $this->assertTrue($this->model->isDirty());
        $this->assertTrue($this->model->hasExistingField('newField'));
        $this->assertFalse($this->model->hasExistingField('nonExistingField'));
        $this->assertTrue(count($this->model->getDirtyFields()) === 1);
    }
}
