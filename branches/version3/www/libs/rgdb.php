<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class RGDB extends mysqli {
	function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost') {
		$this->dbuser = $dbuser;
		$this->dbpassword = $dbpassword;
		$this->dbname = $dbname;
		$this->dbhost = $dbhost;
		$this->connected = false;
		$this->in_transaction = 0;
		$this->show_errors = true;
	}

	function hide_errors() {
		$this->show_errors = false;
	}

	function show_errors() {
		$this->show_errors = true;
	}

	function transaction() {
		if ($this->in_transaction == 0) {
    			$this->query('START TRANSACTION');
		}
		$this->in_transaction++;
		return $this->in_transaction;
	}

	function commit() {
		$this->in_transaction--;
		if ($this->in_transaction == 0) {
    			parent::commit();
		}
		return $this->in_transaction;
	}

	// Reset the connection to the slave if it was using the master
	function barrier() {
	}

	function connect() {
		if ($this->connected) return;
		if ($this->persistent) {
			// PHP 5.2 does not support mysqli persistent connections, so we use the standard
			//$this->dbh_select = @mysqli_connect('p:'.$this->dbhost, $this->dbuser,$this->dbpassword);
			@parent::__construct($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
		} else {
			@parent::__construct($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
		}
		if ($this->connect_error) {
			$this->print_error( _('Error conectando a la BBDD, volvemos en unos segundos, seguramente estamos actualizando el sistema'));
			die;
		}
		$this->connected = true;
		$this->set_charset('utf8');
		if ($this->dbname) $this->select_db($this->dbname);
	}

	function escape($str) {
		$this->connect();
		return $this->real_escape_string(stripslashes($str));
	}

	function print_error($str = "") {
		if ($this->show_errors) echo "$str ($this->error)\n";
	}

	function flush() {
		$this->last_result = null;
	}

	function query($query) {
		$is_select = preg_match("/^ *(select|show)\s/i",$query);

		$this->connect();
		
		// Flush cached values..
		$this->flush();

		$result = @parent::query($query);

		if (!$result) {
			$this->print_error();
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

		return $this->affected_rows;
	}

	function get_var($query=null,$x=0,$y=0) {

		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query);
		}

		// Extract var out of cached results based x,y vals
		if ( $this->last_result[$y] ) {
			$values = array_values(get_object_vars($this->last_result[$y]));
		}

		// If there is a value return it else return null
		return (isset($values[$x]) && $values[$x]!=='')?$values[$x]:null;
	}

	function get_object($query,$class) {
		$this->connect();
		$result = parent::query($query);
		if ( ! $result ) {
			$this->print_error();
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

		return $this->last_result[$y]?$this->last_result[$y]:null;
	}

	//	Function to get 1 column from the cached result set based in X index
	function get_col($query=null,$x=0) {

		// If there is a query then perform it if not then use cached results..
		if ( $query ) {
			$this->query($query);
		}

		// Extract the column values
		for ( $i=0; $i < count($this->last_result); $i++ ) {
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

}
?>
