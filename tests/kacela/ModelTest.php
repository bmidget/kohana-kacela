<?php
/**
 * @author noah
 * @date 10/5/12
 */
class ModelTest extends Kacela_Unittest_Database_TestCase
{
	protected $model = null;

	public function setUp()
	{
		parent::setUp();

		$this->model = new Model_House(Kacela::instance(), Kacela::load('house'));
	}

	/**
	 * Returns the test dataset.
	 *
	 * @return PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet()
	{
		return $this->createArrayDataSet(array());
	}

	public function testGetForm()
	{
		$this->assertInstanceOf('Formo_Form', $this->model->get_form());
	}

}
