<?php

namespace Peyote;

/**
 * Building a SELECT query.
 *
 * The join(), select(), and limit() functions should just be passthrus, but I
 * was getting an error when naming a function the same as the class (php4 FTW!)
 * so I had to just move the methods here and call back. Lame I know, but the
 * only other choice was to go back down to 5.2 and name everything with
 * underscores, and I didn't want to do that, so here we are...
 *
 * @package    Peyote
 * @author     Dave Widmer <dave@davewidmer.net>
 */
class Select extends \Peyote\Query
{
	/**
	 * @var \Peyote\Join  The join object
	 */
	protected $join = null;

	/**
	 * @var \Peyote\Where  The where object
	 */
	protected $where = null;

	/**
	 * @var \Peyote\Group  The group object
	 */
	protected $group_by = null;

	/**
	 * @var \Peyote\Order  The order object
	 */
	protected $order_by = null;

	/**
	 * @var \Peyote\Limit  The limit object
	 */
	protected $limit = null;

	/**
	 * @var array  A list of traits that the query can passthru
	 */
	protected $traits = array('join', 'where', 'group_by', 'order_by', 'limit');

	/**
	 * @var boolean  Run a distinct query?
	 */
	private $is_distinct = false;

	/**
	 * @var array  A list of columns to search for
	 */
	private $columns = array();

	/**
	 * Optionally sets the table name and initializes the internal class
	 * properties.
	 *
	 * @param string $table  The name of the table
	 */
	public function __construct($table = null)
	{
		$this->join = new \Peyote\Join;
		$this->where = new \Peyote\Where;
		$this->group_by = new \Peyote\Group;
		$this->order_by = new \Peyote\Order;
		$this->limit = new \Peyote\Limit;

		parent::__construct($table);
	}

	/**
	 * Run a SELECT DISTINCT query.
	 *
	 * @return \Peyote\Select
	 */
	public function distinct()
	{
		$this->is_distinct = true;
		return $this;
	}

	/**
	 * Specify the columns to select.
	 *
	 * @param  string ...  Any number of string column names
	 * @return \Peyote\Select
	 */
	public function columns()
	{
		return $this->columnsArray(func_get_args());
	}

	/**
	 * Specify the columns to select as an array.
	 *
	 * @param  array $columns  The columns to select
	 * @return \Peyote\Select
	 */
	public function columnsArray(array $columns)
	{
		$this->columns = $columns;
		return $this;
	}

	/**
	 * Add a join.
	 *
	 * @param  string $table  The table name
	 * @param  string $type   The type of join
	 * @return \Peyote\Select
	 */
	public function join($table, $type = null)
	{
		$this->join->addJoin($table, $type);
		return $this;
	}

	/**
	 * Adds a WHERE clause.
	 *
	 * @param  string $column  The column name
	 * @param  string $op      The comparison operator
	 * @param  mixed  $value   The value to bind
	 * @return \Peyote\Select
	 */
	public function where($column, $op, $value)
	{	
		$this->where->andWhere($column, $op, $value);
		return $this;
	}

	/**
	 * Sets the limit.
	 *
	 * @param  int $num  The number to limit the queries to
	 * @return \Peyote\Select
	 */
	public function limit($num)
	{
		$this->limit->setLimit($num);
		return $this;
	}

	/**
	 * Gets all of the bound parameters for this query.
	 *
	 * @return array
	 */
	public function getParams()
	{
		/**
		 * Instead of writing some crazy magic foo to pull the params
		 * automatically, I'm just overridding thid function to get the where
		 * params.
		 *
		 * Where is the only trait that will have params anyway...
		 */
		return $this->where->getParams();
	}

	/**
	 * Compiles the query into raw SQL
	 *
	 * @return  string
	 */
	public function compile()
	{
		$sql = array("SELECT");

		if ($this->is_distinct)
		{
			$sql[] = "DISTINCT";
		}

		if (empty($this->columns))
		{
			$sql[] = "*";
		}
		else
		{
			$sql[] = join(', ', $this->columns);
		}

		$sql[] = "FROM";
		$sql[] = $this->table;

		foreach ($this->traits as $trait)
		{
			$compiled = $this->{$trait}->compile();
			if ($compiled !== "")
			{
				$sql[] = $compiled;
			}
		}

		return join(' ', $sql);
	}

}