<?php
	// IMPORTANT: this is a reduced version of ezdb1.php
	// Its purpose is SPEED, only SPEED, use ezdb1.php if you want debug information.
	// YOU HAVE BEEEN WARNED. 
	//				-- ricardo galli
	//
	// ==================================================================
	//  Author: Justin Vincent (justin@visunet.ie)
	//	Web: 	http://php.justinvincent.com
	//	Name: 	ezSQL
	// 	Desc: 	Class to make it very easy to deal with mySQL database connections.
	//
	// !! IMPORTANT !!
	//
	//  Please send me a mail telling me what you think of ezSQL
	//  and what your using it for!! Cheers. [ justin@visunet.ie ]
	//
	// ==================================================================
	// User Settings -- CHANGE HERE

	//define("EZSQL_DB_USER", $globals['db_user']);			// <-- mysql db user
	//define("EZSQL_DB_PASSWORD", $globals['db_password']);		// <-- mysql db password
	//define("EZSQL_DB_NAME", $globals['db_name']);		// <-- mysql db pname
	//define("EZSQL_DB_HOST", $globals['db_server']);	// <-- mysql server host

	// ==================================================================
	//	ezSQL Constants
	define("EZSQL_VERSION","1.5");

	// ==================================================================
	//	The Main Class

	class db {

		var $show_errors = true;
		var $num_queries = 0;	
		var $col_info;
		var $dbuser;
		var $dbpassword;
		var $dbname;
		var $dbhost;
		var $persistent;
		var $dbh = false;


		// ==================================================================
		//	DB Constructor - connects to the server and selects a database

		function db($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost') {
			$this->dbuser = $dbuser;
			$this->dbpassword = $dbpassword;
			$this->dbname = $dbname;
			$this->dbhost = $dbhost;
		}

		function connect() {
			if ($this->persistent) {
				$this->dbh = @mysql_pconnect($this->dbhost, $this->dbuser,$this->dbpassword);
			} else {
				$this->dbh = @mysql_connect($this->dbhost, $this->dbuser,$this->dbpassword);
			}

			if ( ! $this->dbh ) {
				echo _('Error conectando a la BBDD, el Cabal nos castigará, pero nosotros ya nos estamos flagelando'). "\n";
				die;
			}
			if (!empty($this->dbname)) $this->select($this->dbname);
		}

		// ==================================================================
		//	Select a DB (if another one needs to be selected)

		function select($db) {
			if (!$this->dbh)  $this->connect();
			if ( !@mysql_select_db($db,$this->dbh)) {
				$this->print_error("<ol><b>Error selecting database <u>$db</u>!</b><li>Are you sure it exists?<li>Are you sure there is a valid database connection?</ol>");
			}
		}

		// ====================================================================
		//	Format a string correctly for safe insert under all PHP conditions
		
		function escape($str) {
			if (!$this->dbh)  $this->connect();
			return mysql_real_escape_string(stripslashes($str), $this->dbh);
		}

		// ==================================================================
		//	Print SQL/DB error.

		function print_error($str = "") {
			
			// All erros go to the global error array $EZSQL_ERROR..
			global $EZSQL_ERROR;

			if (!$this->dbh)  $this->connect();
			// If no special error string then use mysql default..
			if ( !$str ) {
				$str = mysql_error($this->dbh);
				$error_no = mysql_errno($this->dbh);
			}
			
			// Log this error to the global array..
			$EZSQL_ERROR[] = array 
							(
								"error_str"  => $str,
								"error_no"   => $error_no
							);

			// Is error output turned on or not..
			if ( $this->show_errors ) {
				// If there is an error then take note of it
				print "<blockquote><font face=arial size=2 color=ff0000>";
				print "<b>SQL/DB Error --</b> ";
				print "[<font color=000077>$str</font>]";
				print "</font></blockquote>";
			} else {
				return false;	
			}
		}

		// ==================================================================
		//	Turn error handling on or off..

		function show_errors() {
			$this->show_errors = true;
		}
		
		function hide_errors() {
			$this->show_errors = false;
		}

		// ==================================================================
		//	Kill cached query results

		function flush() {

			// Get rid of these
			$this->last_result = null;
			$this->col_info = null;

		}

		// ==================================================================
		//	Basic Query	- see docs for more detail

		function query($query) {
			
			if (!$this->dbh)  $this->connect();

			// For reg expressions
			$query = trim($query); 
			
			// initialise return
			$return_val = 0;

			// Flush cached values..
			$this->flush();

			// Perform the query via std mysql_query function..
			$this->result = @mysql_query($query,$this->dbh);
			$this->num_queries++;

			// If there is an error then take note of it..
			if ( mysql_error() ) {
				$this->print_error();
				return false;
			}
			
			// Query was an insert, delete, update, replace
			if ( preg_match("/^(insert|delete|update|replace)\s+/i",$query) ) {
				$this->rows_affected = mysql_affected_rows();
				
				// Take note of the insert_id
				if ( preg_match("/^(insert|replace)\s+/i",$query) ) {
					$this->insert_id = mysql_insert_id($this->dbh);	
				}
				
				// Return number fo rows affected
				$return_val = $this->rows_affected;
			} else {
			// Query was an select
				
				// Take note of column info	
				$i=0;
				while ($i < @mysql_num_fields($this->result)) {
					$this->col_info[$i] = @mysql_fetch_field($this->result);
					$i++;
				}
				
				// Store Query Results	
				$num_rows=0;
				while ( $row = @mysql_fetch_object($this->result) ) {
					// Store relults as an objects within main array
					$this->last_result[$num_rows] = $row;
					$num_rows++;
				}

				@mysql_free_result($this->result);

				// Log number of rows the query returned
				$this->num_rows = $num_rows;
				
				// Return number of rows selected
				$return_val = $this->num_rows;
			}

			return $return_val;
		}

		// ==================================================================
		//	Get one variable from the DB - see docs for more detail

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

		// ==================================================================
		//	Get one row from the DB - see docs for more detail

		function get_row($query=null,$y=0) {

			// If there is a query then perform it if not then use cached results..
			if ( $query ) {
				$this->query($query);
			}

			return $this->last_result[$y]?$this->last_result[$y]:null;
		}

		// ==================================================================
		//	Function to get 1 column from the cached result set based in X index
		// se docs for usage and info

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

		// ==================================================================
		// Return the the query as a result set - see docs for more details

		function get_results($query=null) {

			// Log how the function was called
			$this->func_call = "\$db->get_results(\"$query\", $output)";

			// If there is a query then perform it if not then use cached results..
			if ( $query ) {
				$this->query($query);
			}

			// Send back array of objects. Each row is an object
			return $this->last_result;
		}


		// ==================================================================
		// Function to get column meta data info pertaining to the last query
		// see docs for more info and usage

		function get_col_info($info_type="name",$col_offset=-1) {

			if ( $this->col_info ) {
				if ( $col_offset == -1 ) {
					$i=0;
					foreach($this->col_info as $col ) {
						$new_array[$i] = $col->{$info_type};
						$i++;
					}
					return $new_array;
				} else {
					return $this->col_info[$col_offset]->{$info_type};
				}

			}

		}
}
?>
