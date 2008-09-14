<?php




/**
 * Class for db result
 */
class DbResultNode implements ArrayAccess
{


	/** @var array */
	private $keys = array();


	/**
	 * Constructor
	 * @param   array   data
	 * @return  void
	 */
	public function __construct(array $data)
	{
		$i = 0;
		foreach ($data as $key => $val) {
			$this->$key = $val;
			$this->keys[$i++] = $key;
		}
	}


	/**
	 * Magic method
	 * @return  void
	 */
	public function __get($name)
	{
		throw new DbResultException("Undefined field '$name'.");
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetSet($key, $value)
	{
		$this->{$key} = $value;
	}


	/**
	 * Array-access interface
	 * @return  FormItem
	 */
	public function offsetGet($key)
	{
		if (is_int($key) && isset($this->keys[$key]))
			return $this->{$this->keys[$key]};
		else
			return $this->{$key};
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetUnset($key)
	{
		throw new DbResultException("You can not unset the '$key'.");
	}


	/**
	 * Array-access interface
	 * @return  void
	 */
	public function offsetExists($key)
	{
		if (is_int($key))
			return isset($this->keys[$key]);
		else
			return isset($this->{$key});
	}


}