<?php

use PHPSimpleLib\Core\Data\EnumValidatorRules;
use PHPUnit\Framework\TestCase;
use PHPSimpleLib\Core\Data\ValidatedModel;

class ValidatedTestModel extends ValidatedModel
{
    protected $fieldsForValidation = array(
        'anEmail' => array(
            EnumValidatorRules::IS_EMAIL => array(),
        ),
        'aNumber' => array(
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'aRequiredNumber' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'aHtmlStringForEscaping' => array(
            EnumValidatorRules::FILTER_ENCODE_HTML => array(),
        ),
        'aHtmlStringForStriping' => array(
            EnumValidatorRules::FILTER_STRIP_HTML => array(),
        ),
    );
}

final class ValidatedModelTest extends TestCase
{
    /**
     * Model object for testing
     *
     * @var \PHPSimpleLib\Core\Data\Model
     */
    protected $model = null;

    private $validDataSet = array(
        'anEmail' => 'fred@zeroline.me',
        'aNumber' => 4711,
        'aRequiredNumber' => 4712,
        'aHtmlStringForEscaping' => '<script>alert("Hello")</script> World',
        'aHtmlStringForStriping' => '<script>alert("Hello")</script> World',
    );

    protected function setUp() : void
    {
        $this->model = new ValidatedTestModel(array());
    }

    public function testClass()
    {
        $this->assertInstanceOf(
            ValidatedModel::class,
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

    public function testValidModel() {
        $validModel = new ValidatedTestModel($this->validDataSet);

        $this->assertTrue($validModel->isValid());
    }

    public function testInValidModelEmail() {
        $invalidModel = new ValidatedTestModel($this->validDataSet);
        $invalidModel->anEmail = 'this.is.not_a_valid@address!"';

        $this->assertFalse($invalidModel->isValid());
    }

    public function testInValidModelNumber() {
        $invalidModel = new ValidatedTestModel($this->validDataSet);
        $invalidModel->aNumber = 'iamastring';

        $this->assertFalse($invalidModel->isValid());
    }

    public function testInValidModelRequiredNumber() {
        $invalidModel = new ValidatedTestModel($this->validDataSet);
        $invalidModel->aRequiredNumber = null;

        $this->assertFalse($invalidModel->isValid());
    }

    public function testModelFilterEscape() {
        $expectedString = '&lt;script&gt;alert(&quot;Hello&quot;)&lt;/script&gt; World';
        $invalidModel = new ValidatedTestModel($this->validDataSet);

        $invalidModel->filter();

        $this->assertEquals($invalidModel->aHtmlStringForEscaping, $expectedString);
    }

    public function testModelFilterStrip() {
        $expectedString = 'alert("Hello") World';
        $invalidModel = new ValidatedTestModel($this->validDataSet);

        $invalidModel->filter();

        $this->assertEquals($invalidModel->aHtmlStringForStriping, $expectedString);
    }

    public function testInvalidModelFilterStrip() {
        $expectedString = '<script>alert("Hello")</script> World';
        $invalidModel = new ValidatedTestModel($this->validDataSet);

        $invalidModel->filter();

        $this->assertNotEquals($invalidModel->aHtmlStringForStriping, $expectedString);
    }
}
