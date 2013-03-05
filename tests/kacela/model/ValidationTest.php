<?php
/**
 * @user: noah
 * @date 3/4/13
 */

class Model_ValidationTest extends Kacela_Unittest_Database_TestCase
{

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $stub;

	public function setUp()
	{
		parent::setUp();

		$this->stub = $this->getMockBuilder('Model_Test')
			->setMethods(array('rules'))
			->setConstructorArgs(array(Kacela::instance(), Kacela::load('test'), array()))
			->getMock();
	}

	public function providerValidateWithRulesNonClosure()
	{
		return array
		(
			array('testing', true),
			array(1234, false),
			array(false, false)
		);
	}

	public function testValidate()
	{
		$model = Kacela::factory('test');

		$model->set_data
		(
			array
			(
				'test_name' => 'Test Name',
			)
		);

		$this->assertTrue($model->validate());
	}

	/**
	 * @param $value
	 * @param $expected
	 * @dataProvider providerValidateWithRulesNonClosure
	 */
	public function testValidateWithRulesNonClosure($value, $expected)
	{
		$rules = array
		(
			'test_name' => array
			(
				array('alpha', array(':value'))
			)
		);

		$this->stub
			->expects($this->atLeastOnce())
			->method('rules')
			->will($this->returnValue($rules));

		$this->stub->test_name = $value;

		$this->assertSame($expected, $this->stub->validate(), print_r($this->stub->errors, true));
	}

	public function testValidateWithRulesClosure()
	{
		$rules = array
		(
			''
		);
	}

	public function testValidateWithRulesVirtualField()
	{
		$rules = array
		(
			'virtual' => array
			(
				array('alpha', array(':value')),
				array('not_empty', array(':value'))
			)
		);

		$this->stub
			->expects($this->atLeastOnce())
			->method('rules')
			->will($this->returnValue($rules));

		$this->stub->test_name = 'fine';

		$this->assertTrue($this->stub->validate(), print_r($this->stub->errors, true));
	}


}