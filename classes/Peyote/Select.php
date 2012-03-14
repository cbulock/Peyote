<?php

namespace Peyote;

/**
 * A class that builds SELECT statments.
 *
 * @link       http://dev.mysql.com/doc/refman/5.0/en/select.html
 *
 * @package    Peyote
 * @author     Dave Widmer <dave@davewidmer.net>
 */
class Select extends \Peyote\Base
{
	/**
	 * @var  \Peyote\Join  The join object
	 */
	protected $join;

	/**
	 * @var \Peyote\Where  The where clause
	 */
	protected $where;

	/**
	 * @var \Peyote\GroupBy  The group by clause 
	 */
	protected $group_by;

	/**
	 * @var \Peyote\Having  The having clause 
	 */
	protected $having;

	/**
	 * @var \Peyote\OrderBy  The order by clause
	 */	
	protected $order_by;

	/**
	 * @var \Peyote\Limit  The limit clause
	 */
	protected $limit;

	/**
	 * @var array  A list of columns to select
	 */
	private $columns = array();

	/**
	 * Create a new Update instance.
	 *
	 * @param mixed         $table  The table name OR array($table, $alias)
	 * @param \Peyote\Where $where  Any initial where clause
	 * @param \Peyote\Join  $join   Any initial join info
	 */
	public function __construct($table = "", \Peyote\Where $where = null, \Peyote\Join $join = null)
	{
		$this->table($table);
		$this->where = ($where !== null) ? $where : new \Peyote\Where;
		$this->join = ($join !== null) ? $join : new \Peyote\Join;
		$this->group_by = new \Peyote\GroupBy;
		$this->having = new \Peyote\Having;
		$this->order_by = new \Peyote\OrderBy;
		$this->limit = new \Peyote\Limit;

		parent::__construct();
	}

	/**
	 * Sets the columns to search for data.
	 * 
	 * You can also pass in \Peyote\Expression instances to add 
	 * advanced functionality to the parameters.
	 *
	 * @param  mixed $columns  Any number of columns
	 * @return $this
	 */
	public function columns($columns)
	{
		return $this->columns_array(func_get_args());
	}

	/**
	 * Sets the list of columns as an array.
	 *
	 * You can also pass in \Peyote\Expression instances to add 
	 * advanced functionality to the parameters.
	 *
	 * @param  array $columns  The columns in an array
	 * @return $this
	 */
	public function columns_array(array $columns)
	{
		foreach ($columns as $c)
		{
			$this->columns[] = (is_array($c)) ? "{$c[0]} AS {$c[1]}": $c;
		}

		return $this;
	}

	/**
	 * Compiles the query into raw SQL
	 *
	 * @return  string
	 */
	public function compile()
	{
		$sql = array("SELECT");

		// Column processing
		if (empty($this->columns) === true)
		{
			$sql[] = "*";
		}
		else
		{
			$columns = array();
			foreach ($this->columns as $c)
			{
				$columns[] = $c;
			}

			$sql[] = implode(", ", $columns);
		}

		// Add the table
		$sql[] = "FROM";
		$sql[] = $this->table();

		foreach ($this->traits() as $prop)
		{
			$value = $this->{$prop}->compile();
			if ($value !== "")
			{
				$sql[] = $value;
			}
		}

		return implode(" ", $sql);
	}

	/**
	 * Get the class properties to use as "traits".
	 *
	 * @return array
	 */
	protected function traits()
	{
		return array("join","where","group_by","having","order_by","limit");
	}

}
