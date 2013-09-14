<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class RGDB extends mysqli {
	const POINT_KEY = "rgdb_savepoint_";

	function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost') {
		$this->dbuser = $dbuser;
		$this->dbpassword = $dbpassword;
		$this->dbname = $dbname;
		$this->dbhost = $dbhost;
		$this->connected = false;
		$this->in_transaction = 0;
		$this->show_errors = true;
		$this->initial_query = false;
		$this->connect_timeout = 10;
		// Check the IP is not banned before doing anything more
		$this->ban_checked = check_ip_noaccess(1); // 1 == only cache

	}

	function __destruct() {
		$this->close();
	}

	function close() {
		if (! $this->connected) return;

		// Rollback dangling transactions
		if ($this->transactions > 0) {
			parent::rollback();
			syslog(LOG_INFO, "Dangling transactions, rollback forced ".$_SERVER['SCRIPT_NAME']);
		}
		parent::close();
		$this->connected = false;
	}

	function hide_errors() {
		$this->show_errors = false;
	}

	function show_errors() {
		$this->show_errors = true;
	}
	
	function initial_query($query) {
		$this->initial_query = $query;
		if ($this->connected) {
			return $this->query($query);
		}
		return false;
	}

	function savepoint_name() {
		if ($this->in_transaction > 1) {
			return self::POINT_KEY.$this->in_transaction;
		}
		return '';
	}
	
	function transaction() {
		$this->in_transaction++;
		//syslog(LOG_INFO, __FUNCTION__ ." ".$this->savepoint_name() . " ".$_SERVER['SCRIPT_NAME']);
		if ($this->in_transaction == 1) {
			$this->query('START TRANSACTION');
		} else {
			$r = $this->query("SAVEPOINT ".$this->savepoint_name());
			if (!$r) {
				syslog(LOG_INFO, "Error SAVEPOINT ".$this->savepoint_name().' '.$_SERVER['SCRIPT_NAME']);
			}
		}
		return $this->in_transaction;
	}

	function commit() {
		if ($this->in_transaction <= 0) {
			syslog(LOG_INFO, "Error COMMIT, transaction = 0 ".$_SERVER['SCRIPT_NAME']);
			return false;
		}
		//syslog(LOG_INFO, __FUNCTION__ ." ".$this->savepoint_name() . " ".$_SERVER['SCRIPT_NAME']);

		if ($this->in_transaction > 1) {
			$r = $this->query('RELEASE SAVEPOINT '.$this->savepoint_name());
		} else {
			$r = parent::commit();
		}

		if (! $r) {
			syslog(LOG_INFO, "Error commit/RELEASE SAVEPOINT ".$this->savepoint_name().' '.$_SERVER['SCRIPT_NAME']);
		}
		$this->in_transaction--;
		return $r;
	}

	function rollback() {
		if ($this->in_transaction <= 0) {
			syslog(LOG_INFO, "Error ROLLBACK, transaction = 0 ".' '.$_SERVER['SCRIPT_NAME']);
			return false;
		}
		//syslog(LOG_INFO, __FUNCTION__ ." ".$this->savepoint_name() . " ".$_SERVER['SCRIPT_NAME']);

		if ($this->in_transaction > 1) {
			$r = $this->query('ROLLBACK TO '.$this->savepoint_name());
		} else {
			$r = parent::rollback();
		}

		if (! $r) {
			syslog(LOG_INFO, "Error rollback/ROLLBACK TO ".$this->savepoint_name().' '.$_SERVER['SCRIPT_NAME']);
		}
		$this->in_transaction--;
		return $r;
	}

	// Reset the connection to the slave if it was using the master
	function barrier() {
	}

	function connect() {
		if ($this->connected) return;

		@parent::init();
		@parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->connect_timeout);
		if ($this->persistent && version_compare(PHP_VERSION, '5.3.0') > 0) {
			$this->connected = @parent::real_connect('p:'.$this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
		} else {
			$this->connected = @parent::real_connect($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
		}
		if (! $this->connected) {
			header('HTTP/1.1 503 Service Unavailable');
			die;
		}
		$this->set_charset('utf8');

		if (! $this->ban_checked) {
			// Check the IP is not banned before doing anything more
			check_ip_noaccess(2); // 2 == don't check in cache
			$this->ban_checked = true;
		}

		if ($this->initial_query) {
			$this->query($this->initial_query);
		}
	}

	function escape($str) {
		$this->connect();
		return $this->real_escape_string($str);
	}

	function print_error($str = "") {
		if ($this->show_errors) {
			header('HTTP/1.1 503 Database error');
			header('Content-Type: text/plain');
			echo "$str ($this->error)\n";
		}
		syslog(LOG_NOTICE, "DB ($this->dbhost) error $str ".$_SERVER['REQUEST_URI']." ($this->error)");
	}

	function flush() {
		$this->last_result = array();
	}

	function query($query) {
		$is_select = preg_match("/^ *(select|show)\s/i",$query);

		$this->connect();

		// Flush cached values..
		$this->last_result = array();

		$result = @parent::query($query);

		if (!$result) {
			$this->print_error($query);
			return false;
		}

		if ($is_select) {
			$num_rows=0;
			while ( $row = @$result->fetch_object() ) {
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}
			@$result->close();
		}

		return true;
	}

	function object_iterator($query, $class = null) {
		$is_select = preg_match("/^ *(select|show)\s/i",$query);

		$this->connect();

		// query succeeded
		if ($this->real_query($query)) {
			if ($is_select && $this->field_count) {
				// SELECT, SHOW, DESCRIBE
				return new QueryResult($this, $class);
			} else {
				// INSERT, UPDATE, DELETE
				return $this->affected_rows;
			}
		}
		return false;
	}


	function get_var($query=null,$x=0,$y=0) {
		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query);
		}

		// Extract var out of cached results based x,y vals
		if ( isset($this->last_result[$y]) ) {
			$values = array_values(get_object_vars($this->last_result[$y]));
		}

		// If there is a value return it else return null
		return (isset($values[$x]) && $values[$x]!=='')?$values[$x]:null;
	}

	function get_object($query,$class) {
		$this->connect();
		$result = parent::query($query);
		if ( ! $result ) {
			$this->print_error('error un get_object');
			return false;
		}
		$object = $result->fetch_object($class);
		$result->close();
		return $object?$object:null;
	}

	function get_row($query=null,$y=0) {
		if ( $query ) {
			$this->query($query);
		}

		return isset($this->last_result[$y])?$this->last_result[$y]:null;
	}

	//	Function to get 1 column from the cached result set based in X index
	function get_col($query=null,$x=0) {

		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query);
		}

		// Extract the column values
		$new_array = array();
		$n = count($this->last_result);
		for ( $i=0; $i < $n; $i++ ) {
			$new_array[$i] = $this->get_var(null,$x,$i);
		}
		return $new_array;
	}

	// Return the the query as a result set - see docs for more details
	function get_results($query=null) {
		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query);
		}
		// Send back array of objects. Each row is an object
		return $this->last_result;
	}

	function get_enum_values($table, $column) {
		// Retrieve available status values
		$enum = array();
		$row = $this->get_row("SHOW COLUMNS FROM `$table` like '$column'");
		preg_match_all("/'(.*?)'/", $row->Type, $matches);
		if ($matches[1]) {
			foreach ($matches[1] as $v => $str) {
				$enum[$str] = $v+1;
			}
		}
		return $enum;
	}


}

// Iterators inspired from:
//     http://techblog.procurios.nl/k/news/view/33914/14863/Syntactic-Sugar-for-MySQLi-Results-using-SPL-Iterators.html

class ObjectIterator implements Iterator {
	protected $result;
	protected $class;
	protected $position;
	protected $currentRow;

	public function __construct($result, $class= null) {
		$this->Result = $result;
		$this->class = $class;
	}

	public function __destruct() {
		$this->Result->free();
	}

	public function rewind() {
		$this->Result->data_seek($this->position = 0);
		$this->currentRow = $this->Result->fetch_object($this->class);
	}

	public function next() {
		$this->currentRow = $this->Result->fetch_object($this->class);
		++$this->position;
	}

	public function valid() {
		return $this->position < $this->Result->num_rows;
	}

	public function current() {
		$this->currentRow->read = true;
		return $this->currentRow;
	}

	public function key() {
		return $this->position;
	}
}

class QueryResult extends MySQLi_Result implements IteratorAggregate {
	public function __construct($result, $class=null) {
		parent::__construct($result);
		$this->class = $class;
	}

	public function getIterator() {
		return new ObjectIterator($this, $this->class);
	}
}



?>
