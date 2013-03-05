<?php
/**
 * @user: noah
 * @date 11/8/12
 */
abstract class Kacela_Unittest_Database_TestCase extends Kohana_Unittest_Database_TestCase
{
	private $conn;

	public static function setUpBeforeClass()
	{
		$k = Kacela::instance();

		$source = Kacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'db',
				'user' => 'kacela',
				'password' => 'kacela',
				'schema' => 'kacela',
				'host' => 'localhost'
			)
		);

		$test = Kacela::createDataSource(
			array(
				'type' => 'mysql',
				'name' => 'test',
				'user' => 'kacela',
				'password' => 'kacela',
				'schema' => 'kacela-test',
				'host' => 'localhost'
			)
		);

		$k->registerDataSource($source)
			->registerDataSource($test);

		$k->registerNamespace('App', '/var/www/gacela/samples/')
			->registerNamespace('Test', '/var/www/gacela/tests/Test/');

	}

	public function getConnection()
	{
		$test = Kacela::instance()->getDataSource('test');

		$test->loadResource('peeps');

		$r = new \ReflectionClass($test);

		$p = $r->getProperty('_adapter');

		$p->setAccessible(true);

		$p = $p->getValue($test);

		$pr = new \ReflectionClass($p);

		$c = $pr->getProperty('_conn');

		$c->setAccessible(true);

		$pdo = $c->getValue($p);

		if(is_null($this->conn)) {
			$this->conn = $this->createDefaultDBConnection($pdo);
		}

		return $this->conn;
	}

	public function getSetUpOperation()
	{
		$cascadeTruncates = TRUE; //if you want cascading truncates, false otherwise
		//if unsure choose false

		return new \PHPUnit_Extensions_Database_Operation_Composite(array(
			new Kacela_Unittest_Database_Operation_MySQLTruncate($cascadeTruncates),
			\PHPUnit_Extensions_Database_Operation_Factory::INSERT()
		));
	}

	protected function getDataSet()
	{
		return $this->createArrayDataSet(
			array(
				'peeps' => array(),
				'tests' => array()
			)
		);
	}

	/**
	 * Creates a new Array DataSet with the given $array.
	 *
	 * @param string $xmlFile
	 * @return DataSet\ArrayDataSet
	 */
	protected function createArrayDataSet(array $array)
	{
		return new Kacela_Unittest_Database_Dataset_Array($array);
	}
}
