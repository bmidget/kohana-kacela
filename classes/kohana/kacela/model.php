<?php
/**
 * @author noahg
 * @date 6/16/11
 * @brief
 *
 */

use Gacela\Model as M;

abstract class Kohana_Kacela_Model extends M\Model
{

	protected function _formo_rules($field)
	{
		$rules = array();

		if ($field->null === FALSE AND $field->type != 'bool') {
			// Add not_empty rule if it doesn't allow NULL
			$rules[] = array('not_empty');
		}

		if ($field->type == 'int') {
			$rules[] = array('digit');
		}

		if ($field->length) {
			$rules[] = array('max_length', array(':value', $field->length));
		}

		if ($field->type == 'enum') {
			$rules[] = array('in_array', array(':value', $field->values));
		}

		if ($field->type == 'date') {
			$rules[] = array('date');
		}

		return $rules;
	}

	protected function _formo_field($field, $data, $value)
	{
			$array = array('alias' => $field, 'val' => $value, 'driver' => 'input');

			switch ($data->type)
			{
				case 'enum':
					$keys = $data->values;
					array_walk($keys, function(&$k) { $k = ucfirst($k); });

					$array['driver'] = 'select';
					$array['opts'] = array_combine($data->values, $keys);
					break;
				case 'bool':
					$array['driver'] = 'checkbox';
					break;
				case 'date':
					$array['val'] =  \Format::date($this->$field);
					$array['attr']['class'] = 'datepicker';
					break;
				default:
					if($data->length <= 10) {
						$class = 'small';
					} elseif ($data->length <= 20) {
						$class = 'med';
					} else {
						$class = 'big';
					}

					$array['attr']['class'] = $class;
					break;
			}

			$label = explode('_', $field);
			array_walk($label, function(&$word) { $word = ucfirst($word); });
			$array['label'] = join(' ', $label);

			return \Formo::factory($array);
		}

	protected function _get_errors()
	{
		return parent::_getErrors();
	}

	/**
	 * @return \Gacela\Mapper\Mapper
	 */
	protected function _mapper()
	{
		if($this->_mapper instanceof Kacela_Mapper) {
			return $this->_mapper;
		}

		if(is_string($this->_mapper)) {
			$class = $this->_mapper;
		} else {
			$class = explode("_", get_class($this));
			$class = end($class);
		}

		$this->_mapper = Kacela::load($class);

		return $this->_mapper;
	}

	/**
	 * @throws \Exception
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$method = '_get_' .$key;
		if (method_exists($this, $method)) {
			return $this->$method();
		} elseif (array_key_exists($key, $this->_relations)) {
			return $this->_mapper()->findRelation($key, $this->_data);
		} else {
			if(property_exists($this->_data, $key)) {
				return $this->_data->$key;
			}
		}

		throw new \Exception("Specified key ($key) does not exist!");
	}

	/**
	 * @param  string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		$method = '_isset_'.$key;

		if (method_exists($this, $method)) {
			return $this->$method($key);
		} elseif (isset($this->_relations[$key])) {
			$relation = $this->$key;

			if ($relation instanceof \Gacela\Collection\Collection) {
				return count($relation) > 0;
			} else {
				if (!is_array($this->_relations[$key])) {
					return isset($relation->{$this->_relations[$key]});
				} else {
					// Need to support multi-field key relations
				}
			}
		}

		return isset($this->_data->$key);
	}

	/**
	 * @param  $key
	 * @param  $val
	 * @return void
	 */
	public function __set($key, $val)
	{
		$method = '_set_'.$key;

		if (method_exists($this, $method)) {
			$this->$method($val);
		} else {
			parent::__set($key, $val);
		}
	}

	public function get_form(array $fields)
	{
		$form = \Formo::form();

		foreach ($fields as $field)
		{
			$form->add($this->_formo_field($field, $this->_fields[$field], $this->$field));
		}

		foreach ($form->as_array() as $alias => $val)
		{
			if ($field = \Arr::get($this->_fields, $alias))
			{
				$rules = $this->_formo_rules($field);

				if(!empty($rules))
				{
					$form->add_rule(array(
						$alias => $rules
					));
				}
			}
		}

		return $form;
	}

	/**
	 * @return array
	 */
	public function rules() {}

	public function save($data = null)
	{
		if($data instanceof \Formo)
		{
			$data = $data->val();
		}

		return parent::save($data);
	}

	public function set_data(array $data)
	{
		return $this->setData($data);
	}

	public function validate(array $data = null)
	{
		if($data instanceof \Formo_Form)
		{
			$data = $data->val();
		}

		$rs = parent::validate($data);

		$rules = $this->rules();

		if(!empty($rules))
		{
			$_validation = Validation::factory($this->_data)
				->bind(':model', $this)
				->bind(':original_values', $this->_originalData)
				->bind(':changed', $this->_changed);

			foreach ($this->rules() as $field => $rules)
			{
				$_validation->rules($field, $rules);
			}

			if($_validation->check() === false)
			{
				$rs = false;
				$this->_errors = array_merge($this->_errors, $_validation->errors());
			}
		}

		return $rs;
	}

}
