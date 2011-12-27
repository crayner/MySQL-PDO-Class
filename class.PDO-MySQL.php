<?php 
/**
  * MySQL Class File
  * @package craig
  *
  * @version 27th December 2011
  * @copyright Craig Rayner 2009-2011<br />
  *  Vocational Education Record Sysem for Registered Training Organisation: Australia.<br />
  *  Copyright (C) 2004-2011  Craig A. Rayner<br />
  *  <br />
  *  This program is free software: you can redistribute it and/or modify<br />
  *  it under the terms of the GNU General Public License as published by<br />
  *  the Free Software Foundation, either version 3 of the License, or<br />
  *  any later version.<br />
  *  <br />
  *  This program is distributed in the hope that it will be useful,<br />
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of<br />
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br />
  *  GNU General Public License for more details.<br />
  *  <br />
  *  You should have received a copy of the GNU General Public License<br />
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */
/**
  * MySQL Database Record Manipulator in PHP using PDO
  *
  * @author Craig Rayner
  * @copyright Craig Rayner 2009-2009
  * @since 26th June 2009
  * @package craig
  *
  * @version 27th December 2011
  *
    Information Record Sysem for Registered Training Organisation: Australia.
    Copyright (C) 2004-2011  Craig A. Rayner

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */
class mysql_PDO {
/**
  * Version: Current Class Version Number
  * @access public
  * @var string
  */
	var $version = '11th December 2011';
/**
  * Data Base Name
  * @access public
  * @var string
  */
	var $DataBaseName;
/**
  * Data Base Index
  * @access public
  * @var integer
  */
	var $DataBaseIndex;
/**
  * Site is under Development
  * @access public
  * @var boolean
  */
	var $TestMode = false;
/**
  * Which Method was last called
  * @access public
  * @var string
  */
	var $FunctionCalled;
/**
  * Last MySQL Error
  * @access public
  * @var string
  */
	var $error;
/**
  * Last MySQL Error Number
  * @access public
  * @var integer
  */
	var $errno;
/**
  * Last MySQL Error Number
  * @access public
  * @var integer
  */
	var $PDOErrno;
/**
  * Last MySQL Access Status
  * @access public
  * @var boolean
  */
	var $ok;
/**
  * Pointer to the Current (or Next) Row
  * @access public
  * @var integer
  */
	var $thisrow;
/**
  * Total number of rows in the result set.
  * @access public
  * @var integer
  */
	var $lastrow;
/**
  * Next available AUTOINCREMENT
  * @access public
  * @var integer
  */
	var $next_id;
/**
  * Number of Rows Altered by last MySQL Access
  * @access public
  * @var integer
  */
	var $AffectedRows;
/**
  * Has Purge Changes been done
  * @access private
  * @var boolean
  */
	var $PurgeChangesDone;
/**
  * List of Log Exceptions
  * @access public
  * @var array
  */
	var $DoNotLog;
/**
  * Last Query
  * @access public
  * @var string
  */
	var $LastQuery;
/**
  * MySQLi
  * @access public
  * @var object
  */
	var $PDO;
/**
  * Result Object for Queries Executed
  * @access public
  * @var object
  */
	var $result;
/**
  * Query to be Executed or Accessed.
  * @access public
  * @var string
  */
	var $query;
/**
  * Table Name
  * @access public
  * @var string
  */
	var $table;
/**
  * The Identifier Name (Unique) in a Table
  * @access public
  * @var string
  */
	var $identifier;
/**
  * Field Data from table
  * @access public
  * @var array
  */
	var $field;
/**
  * Data retireved from Select
  * @access public
  * @var array
  */
	var $fetch;
/**
  * Field Information
  * @access public
  * @var array
  */
	var $FieldData;
/**
  * Character Set
  * @access public
  * @var string
  */
	var $CharacterSet = 'utf8'; 
/**
  * Collate
  * @access public
  * @var string
  */
	var $Collate = 'utf8_bin';
/**
  * PDO Database Access Constructor
  * 
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string The database name to set for this session.
  * @return void
  */
	function mysql_PDO($database = '') {
 
   		global $version;
   		$this->FunctionCalled = 'PDO Record Constructor';
   		$this->DataBaseName = $database;
   		$this->ClearField();
   		if (TEST_MODE) {
     		$this->TestMode = true;
   		}
		if (defined("COLLATE")) {
			$this->Collate = COLLATE;
		}
		if (defined("CHARACTERSET")) {
			$this->CharacterSet = CHARACTERSET;
		}
		$this->LogCall('mysql_record($database = '.strval($database).')');
   		if (isset($version['class.PDO-MySQL.php'])) 
			return;
   		$version['class.PDO-MySQL.php'] = $this->version;
		$this->DoNotLog = array();
		$this->LastQuery = '';
		$this->DoNotLog[] = $this->query = "CREATE TABLE IF NOT EXISTS `".PRE_NAME."changes` (
    			`id` bigint(11) unsigned zerofill NOT NULL auto_increment,
     			`tablename` varchar(40) NOT NULL default '',
     			`tablekey` varchar(30) NOT NULL default '',
     			`changedate` datetime default NULL,
     			`olddata` longtext NOT NULL,
     			`querytype` enum('Update','Insert','Delete') NOT NULL default 'Update',
     			`user` varchar(50) NOT NULL default 'Unknown',
     		PRIMARY KEY  (id)
     		) TYPE=MyISAM";
   		$this->ExecuteQuery($this->query);
		$this->DoNotLog[] = $this->query = "CREATE TABLE IF NOT EXISTS `".PRE_NAME."PurgeData` (
    			`id` INT( 11 ) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    			`TableName` VARCHAR( 250 ) NOT NULL ,
				`TableIdentifier` VARCHAR( 250 ) NOT NULL DEFAULT 'id' ,
    			`method` ENUM( 'Records', 'Date', 'TableSize' ) NOT NULL DEFAULT 'Records',
    			`size` INT( 11 ) NOT NULL DEFAULT '1000',
    			`multiplier` ENUM( 'Nil', 'kB', 'MB' ) NOT NULL DEFAULT 'Nil',
				`RowDate` VARCHAR( 250 ) NULL ,
    		UNIQUE (
    			`TableName`
    		)
    		) ENGINE = MYISAM COMMENT = 'Holds data of Tables to Purge'";
   		$this->ExecuteQuery($this->query);
   		if (! CRON_ON) 
     		$this->PurgeChanges();
 	}
/**
  * Log of the Methods Called.
  *
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string Method Name
  * @return void
  */
  	function LogCall($method) {
	
		if (! $this->TestMode)
			return;
		global $version;
		$x = explode(' ', microtime());
		$version['class.PDO-MySQL.php '.strval(number_format($x[1] + mb_substr($x[0], 1), 6, '.', ''))] = $method;
		return ;
	}
/**
  * Clear Fields and Query
  * 
  * @access public
  * @version 26th June 2009
  * @since 26th June 2009
  * @return void 
  */
	function ClearField() {

		$this->LogCall('ClearField()');
  		unset($this->field, $this->query);
		return ;
	}
/**
  * Execute Query
  *
  * All hell breaks loose as this will execute ANY query<br />
  * It does not record its changes at all.  <b>Still in development.</b><br />
  * sets ok, error, errno
  * @version 5th July 2009
  * @since 26th June 2009
  * @param string $query New query into object, defaults to object->query
  * @return integer Number of affected rows.
  */
	function ExecuteQuery($query = 'NO QUERY') {

		$this->LogCall('ExecuteQuery($query = '.strval($query).')');
  		$this->FunctionCalled = 'Execute Query: ';
  		$this->OpenDatabase();
  		if ($query != "NO QUERY")
    		$this->query = $query;
		$this->LastQuery = $this->query;
  		$this->ClearError();
  		$result = $this->PDO->exec($this->query);
  		$this->AffectedRows = $result;
  		if (intval($this->PDO->errorCode()) !== 0) {
    		$this->SetError("Execute Query: ", $this->PDO->errorCode());
			if ($this->TestMode) 
	  			$this->debug();
  		} else {
			$this->SaveChanges('', 'Execute');
		}
		$result = NULL;
		$this->PDO = NULL;
		unset($result, $this->PDO);
  		return $this->AffectedRows;
	}
/**
  * Open Database
  *
  * Change the following GLOBAL variables to your MySQL requirements with the config data<br />
  * (config data kept in a different file for security reasons.)<br />
  * MYSQL_HOST<br />
  * MYSQL_USER<br />
  * MYSQL_PASS<br />
  * MYSQL_DB
  *
  * @access public
  * @version 31st August 2011
  * @since 26th June 2009
  * @return void
  */
 	function OpenDatabase() {

		$this->LogCall('OpenDatabase()');
   		$this->dBName = explode(',', MYSQL_DB);
   		$HostName = explode(',', MYSQL_HOST);
   		$UserName = explode(',', MYSQL_USER);
   		$PassWord = explode(',', MYSQL_PASS);
   		$this->DataBaseIndex = 0;
   		if ($this->DataBaseName !== '') {
     		$this->DataBaseIndex = current(array_keys($this->dBName, $this->DataBaseName));
   		}
   		$this->DataBaseName = $this->dBName[$this->DataBaseIndex];
		
		try {
			$this->PDO = new PDO(
				'mysql:host='.$HostName[$this->DataBaseIndex].';dbname='.$this->DataBaseName,
				$UserName[$this->DataBaseIndex],
				$PassWord[$this->DataBaseIndex],
				array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".$this->CharacterSet, PDO::ATTR_PERSISTENT => true)
			); 
		} catch (PDOException $e){
			$this->SetError("Error!: " . $e->getMessage());
			$this->debug();
		}
		return ;
 	}
/**
  * Clear the Error Details
  *
  * @version 29th June 2009
  * @since 26th June 2009
  * @return void
  */
  	function ClearError() {
	
		$this->LogCall('ClearError()');
		$this->PDOErrno = 0;
		$this->error = '';
		$this->errno = 0;
		$this->ok = true;
		return ;
	}
/** 
  * Save Changes
  *
  * Saves the changes of an insert or update query into the changes table.
  * @version 26th June 2009
  * @since 26th June 2009
  * @private
  * @param string The data that has changed.
  * @param string The type of data change.
  * @return void
  */
	private function SaveChanges($data, $type){
 
		$this->LogCall('SaveChanges($data = '.strval($data).', $type = '.strval($type).')');
		if ($handle = @gzopen(MYSQL_LOGPATH.'sql'.date('Y-m-d').'.log.gz', 'a9')) {
			if (is_array($this->DoNotLog))
				if (! in_array($this->LastQuery, $this->DoNotLog)) 
					gzwrite($handle, date('His').':'.$this->LastQuery.";\n");
			gzclose($handle);
		}
		if (empty($data))
			return ;
     	$skip = explode(',', SKIP_CHANGES);
   		if (in_array($this->table, $skip)) 
     		return ;
   		$data = addslashes($_SERVER['SCRIPT_FILENAME'].'||'.$data);
   		if (empty($_SESSION['user'])) 
     		$_SESSION['user'] = 'System';
   		$change = "INSERT INTO `".PRE_NAME."changes` 
   			SET `querytype` ='".$type."', 
   				`user` ='".$_SESSION['user']."', 
   				`changedate` ='".date('Y-m-d H:i:s')."', 
   				`olddata` ='".$data."', 
   				`tablename` ='".$this->table."', 
   				`tablekey` ='".$this->field[$this->identifier]."'";
   		$result = $this->PDO->query($change);
		$result = NULL;
		unset($result);
		return ;
	}
/**
  * Initiate Query
  *
  * Same as select_query except this defaults the pointer to the first record of the record set.<br />
  * Sets the same variables as select_query, as this function calls select query after setting the query and thisrow = 0.
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string The new query, defaults to object->query
  * @param integer The pointer value should you wish to retrieve, defaults to first record.
  * @return array Table Fields
  */
	function InitiateQuery($text, $thisrow = -1) {

		$this->LogCall('InitiateQuery($text = '.strval($text).', $thisrow = '.strval($thisrow).')');
  		$this->FunctionCalled = 'Initiate Query: ';
  		$this->ClearField();
  		$this->thisrow = 0;
  		if ($thisrow != -1) 
    		$this->thisrow = $thisrow;
  		$this->SelectQuery($text, $thisrow);
  		return $this->field;
	}
/**
  * Select Query
  *
  * Initiates a query and returns the row indicated by thisrow<br />
  * <br />
  * sets the field array with the results (slashes striped from field) or<br />
  * sets error / errno with the problem.<br />
  * sets ok to indicate success<br />
  * sets query, thisrow, lastrow, result
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string A new query to replace the internal query.
  * @param integer the pointer to the row to retrieve.
  * @return array The table fields
  */
	function SelectQuery($query = "NO QUERY", $thisrow = -1) {

		$this->LogCall('SelectQuery($query = '.strval($query).', $thisrow = '.strval($thisrow).')');
  		$this->FunctionCalled = 'Select Query: ';
  		if ($query === 'Build') {
    		$this->query = $this->BuildSelectQuery();
  		} elseif ($query !== "NO QUERY") {
   		 	$this->query = $query;
  		}
  		if ($thisrow != -1) {
    		$this->thisrow = $thisrow;
  		}
		$this->ClearError();
  		$this->OpenDatabase();
  		$this->result = $this->PDO->query($this->query);
  		$err = $this->PDO->errorCode();
  		if (intval($err) > 0) {
			$this->SetError("Select 01: ".print_r($this->PDO->errorInfo(), true),  $err);
			if ($this->TestMode) {
				switch ($this->errno) {
	  				case 1146:
						break;
					default:
						$this->debug();
				}
			}
  		}
		if (is_object($this->result)) {
			$this->fetch = $this->result->fetchAll(PDO::FETCH_ASSOC);
			$this->lastrow = 0;
			if ($this->ok) {
				$this->lastrow = count($this->fetch);
				if ($this->thisrow > $this->lastrow - 1) 
					$this->thisrow = $this->lastrow - 1;
				if ($this->thisrow < 0) 
					$this->thisrow = 0;
				if (empty($this->fetch[$this->thisrow]) AND $this->lastrow > 0) {
					$this->SetError("Select 02: Failed to set the pointer in the query. Pointer: .".$this->thisrow.' in '.$this->lastrow);
					if ($this->TestMode) 
						$this->debug();
				} else {
					if ($this->lastrow > 0) {
						foreach ($this->fetch[$this->thisrow] as $q=>$w) {
							$this->field[$q] = stripslashes($w);
						} 
					}
				}
			}
		} else {
			$this->thisrow = 0;
			$this->lastrow = 0;
			$this->SetError('The query returned an empty set.', 9997);
			$this->field = array();
			$this->fetch = array();
		}
		if (! isset($this->field))
			$this->field = array();
		$this->result = NULL;
		$this->PDO = NULL;
		unset($this->result, $this->PDO);
  		return $this->field;
	}
/**
  * Build the Select Query
  *
  * @version 26th June 2009
  * @since 26th June 2009
  * @return string  The Query built from the details given.
  */
 	function BuildSelectQuery() {
 
		$this->LogCall('BuildSelectQuery()');
   		if (empty($this->table)) {
			$this->SetError("BuildSelectQuery:  Requires that the table property be set:  $this->table");
	 		$this->debug();
   		}  
   		if (empty($this->select)) {
     		$this->select = '*';
   		}
   
   		$query = 'SELECT ';
   		$ss = explode(",", $this->select);
   		foreach($ss as $q=>$w) {
      		$query .= $this->TestforTableNames(trim($w)).",\n";
   		}
   		$query = mb_substr($query, 0, -2) ;
   		$query .= "\n FROM ";
   		$ss = explode(",", $this->table);
   		foreach($ss as $q=>$w) {
      		$query .= $this->AddTablePrefix($w).",\n";
   		}
   		$query = mb_substr($query, 0, -2) ;
   		// JOIN
   		if (! empty($this->Join)) {
			$query .= "\n ".$this->BuildJoinString();
   		}
   
   		//  WHERE
   		if (! empty($this->where)) {
     		$query .= "\n WHERE ".$this->BuildWhereString();
   		}
   		// ORDERBY
   		if (! empty($this->OrderBy)) {
     		$query .= "\n ORDER BY ".$this->BuildOrderByString();
   		}
   		return $query; 
 	} 
/**
  * Test for Table Names
  *
  * Test for Table Names on the front of Field Names and add prefix if necessary.
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string The field name under test.
  * @return string The modified field name.
  */
	function TestforTableNames($field) {
	
		$this->LogCall('TestforTableNames($field = '.strval($field).')');
		if ($field === '*') 
			return $field;
		if ($field === '(') 
			return $field;
		if ($field === ')') 
			return $field;
		$ss = explode(".", $field);
		if (count($ss) === 2) {
			$field = $this->AddTablePrefix($ss[0]).".`".$ss[1]."`";
		} else {
			$field = '`'.$field.'`';
		}
		if (mb_substr($field, mb_strlen($field)-3, 3) === "`*`") 
			$field = mb_substr($field, 0, mb_strlen($field) - 3)."*";
		return $field;
	}
/** 
  * Add Table Prefix
  *
  * Test for and if necessary add the table prefix to the tablename.<br />
  * The Prefix is defined in the config file as PRE_NAME
  *
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string  The table name to test and add the prefix too.
  * @return string  The corrected table name.
  */
	function AddTablePrefix($table) {
	
		$this->LogCall('AddTablePrefix($table = '.strval($table).')');
		$x = PRE_NAME;
		if (empty($x)) 
			return "`".$table."`";
		if (mb_strpos($table, $x) === 0) 
			return "`".$table."`";
		return "`".$x.$table."`";
	}
/**
  * Build Where String
  *
  * Rules for the Where string array.<br />
  * Each array key must end with one of the following characters:<br />
  * F = Field Name<br />
  * V = Value to test for in the field<br />
  * C = Comparitor between the field and value.  (=, !=, >, <=, LIKE, etc)<br />
  * L = Linking Structure such as (, ), AND, OR, etc
  * @version 26th June 2009
  * @since 26th June 2009
  * @return string The WHERE clause of an SQL query.
  */
	function BuildWhereString() {
	
		$this->LogCall('BuildWhereString()');
		ksort($this->where);
		unset($y, $wh, $f, $c, $l, $v);
		foreach($this->where as $q=>$w) {
			$k = mb_substr($q, 0, -1);
			if (@$y !== $k) {
				if (! empty($f))
					@$wh .= $this->TestforTableNames($f).' '.$c.' '.$v;
				if (! empty($l)) 
					$wh .= "\n ".$l.' ';  
				$y = $k;
				unset($c, $f, $l, $v);
			}
			$t = mb_substr($q, mb_strlen($q) - 1, 1);
			switch ($t) {
				case "F":
					$f = $w;
					break;
				case "V":
					$v = $w;
					break;
				case "C":
					$c = $w;
					break;
				case "L":
					$l = $w;
					break;
				default:
					$this->SetError('Unable to parse the where clause in the SQL query correctly.');
					$this->debug();
			}
		}
		if (! empty($f)) {
			@$wh .= " ".$this->TestforTableNames(@$f).' '.@$c.' '.@$v." ".@$l."\n ";
		} elseif (! empty($l)) {
			$wh .= " ".$l."\n ";
		} else {
			$wh .= "\n ";
		}
		return $wh;
	}
/**
  * Build OrderBy String
  *
  * @version 26th June 2009
  * @since 26th June 2009
  * @return string The ORDERBY clause of an SQL query.
  */
	function BuildOrderByString() {
	
		$this->LogCall('BuildOrderByString()');
		$ss = explode(",", $this->OrderBy);
		unset($ob);
		foreach($ss as $q=>$w) {
			$w = trim($w);
			if (mb_strpos($w, 'ASC') === mb_strlen($w) - 3) {
				$f = $this->TestforTableNames(trim(mb_substr($w, 0, -3)));
				$d = 'ASC';
			} elseif (mb_strpos($w, 'DESC') === mb_strlen($w) - 4) {
				$f = $this->TestforTableNames(trim(mb_substr($w, 0, -4)));
				$d = 'DESC';
			} else {
				unset($d);
				$f = $this->TestforTableNames($w);
			}
			@$ob .= $f.' '.@$d.",\n ";
		}
		$ob = mb_substr($ob, 0, -3)."\n ";
		return $ob;
	}
/**
  * Set the Error Details
  *
  * @version 14th July 2009
  * @since 26th June 2009
  * @param mixed Error Description
  * @param string Error Number
  * @return void
  */
  	function SetError($error, $errno = '9998') {
	
		$this->LogCall('SetError-1($error = '.strval($error).', $errno = '.strval(intval($errno)).')');
		$this->ClearError();
		if (is_object($this->PDO)) {
			if (is_array($this->PDO->errorInfo())) {
				$x = $this->PDO->errorInfo();
				$this->errno = intval($x[1]);
				$this->error = $error .'<br />'.strval($x[2]);
				$this->PDOErrno = intval($x[0]);
			} else {
				$this->error = $error;
				$this->errno = intval($errno);
				$this->PDOErrno = 0;
			}
		} else {
			$this->error = $error;
			$this->errno = intval($errno);
			$this->PDOErrno = 0;
		}
		if ( intval($this->errno) === 0 ){
			$this->error = $error;
			$this->errno = intval($errno);
			$this->PDOErrno = 0;
		}
		$this->ok = false;
		return ;
	}
/**
  * Debug
  *
  * Prints the entire object to the browser.
  * @version 26th June 2009
  * @since 26th June 2009
  * @param boolean Set to false so that program execution does not stop.
  * @return void
  */
	function debug($stop = true) {

		$this->LogCall('debug($stop = '.strval(intval($stop)).')');
  		global $version;
		$x = "MySQL<pre>\n";
		$x .= var_export($this, true);
		$x .= "</pre>\n";
		$x .= "Version<pre>\n";
		$x .= var_export($version, true);
		$x .= "</pre>\n";
		echo $x;
		$x .= "Server<pre>\n";
		$x .= var_export($_SERVER, true);
		$x .= "</pre>\n";
		if (! $this->TestMode) {
			mb_send_mail('webmaster@'.SERVER_NAME, 'MySQL Error: '.SERVER_NAME, $x);
		}
  		if ($stop) {
    		exit();
  		}
	}
/**
  * Retrieve Row from an established query.
  *
  * sets the field array with the results (slashes striped from field) or<br />
  * sets error / errno with the problem.<br />
  * sets ok to indicate success<br />
  * sets thisrow<br />
  * Use this function after setting the query to retrieve successive row, without the overhead of 
  * a new select query to the database, therefore speedier replies.
  * @param boolean Increment thisrow after retrieving the row.
  * @param integer Increment thisrow after reading table row.
  * @version 26th June 2009
  * @since 26th June 2009
  * @return array The fields called in the query.
  */
	function RetrieveRow($inc = false, $thisrow = -1) {
	
		$this->LogCall('RetrieveRow($inc = '.strval($inc).', $thisrow = '.strval($thisrow).')');
  		$this->functionCalled = 'Retrieve Row';
  		if ($thisrow != -1) 
    		$this->thisrow = $thisrow;
		if (! is_array($this->fetch)) {
			$this->SetError('Fetch array is not available in RetrieveRow.');
			$this->debug();
		}
		$this->field = array();
		if (is_array($this->fetch[$this->thisrow]))
			foreach ($this->fetch[$this->thisrow] as $q=>$w) {
				$this->field[$q] = stripslashes($w);
			}
  		if ($inc) 
    		$this->thisrow++;
		if (! is_array($this->field))
			$this->field = array();
  		return $this->field;
	}
/**
  * Extract ENUM array
  *
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string table
  * @param string field
  * @param boolean Alpha-Numeric Sort
  * @return array
  */
  	function ExtractENUMArray($table, $field, $sort = false) {
	
		$this->LogCall('ExtractENUMArray($table = '.strval($table).', $field = '.strval($field).', $sort = '.strval($sort).')');
		$rr = $this->InitiateQuery("SHOW COLUMNS FROM `".$table."` WHERE `field` = '".$field."'");
		if ($this->lastrow !== 1) 
			return array();
		$r = mb_substr($rr['Type'], 6);
		$x = explode("','", mb_substr($r, 0, -2));
		if ($sort)
			sort($x);
		return $x;
	}
/**
  * Save Record
  *
  * Inserts or Updates a record depending on how variables have been set.<br />
  * only updates if field[identifier] != 0<br />
  * if field[identifier] != 0 and table.identifier == 0 then AN insert is done.
  * Will set a field as `RecordChange` = date(Y-m-d H:i:s)
  * @version 17th February 2010
  * @since 26th June 2009
  * @param string New table name, if not set used object table name
  * @param string New Identifier with the table name, or uses object idenetifier name.
  * @param array Adds fields to array to be set to NULL if field value is EMPTY.
  * @access public
  * @return integer The pointer to the record saved.
  */
	function SaveRecord($table = "NO TABLE", $identifier = 'Not Set') {
 
		$this->LogCall('SaveRecord($table = '.strval($table).', $identifier = '.strval($identifier).')');
		$this->functionCalled = 'Save Record: ';
 		if ($table != "NO TABLE") 
			$this->table = $table;
 		if ($identifier != 'Not Set') 
			$this->identifier = $identifier;
 		if (empty($this->identifier)) {
			$this->SetError("The Table Identifier has not been set in MySQL_record.");
   			$this->debug();
 		}
 		if (empty($this->table)) {
   			$this->SetError("The Table Name has not been set in MySQL_record.");
   			$this->debug();
 		}
		//Add a Record Change Field.  Will be deleted if not available.
		$this->field['RecordChange'] = date('Y-m-d H:i:s');
  		//Remove any invalid field from the list of fields.
  		$field = $this->field;
		$col = array();
		if (isset($this->query))
  			$query = $this->query;
  		$thisrow = $this->thisrow;
  		$lastrow = $this->lastrow;
  		if (isset($this->result))
			$result = $this->result;
		$fetch = $this->fetch;
  		$this->InitiateQuery('SHOW COLUMNS FROM `'.$this->table.'`');
		$this->FieldDetails = array();
  		while ($this->thisrow < $this->lastrow) {
    		$rr = $this->RetrieveRow(true);
			$this->FieldDetails[$rr['Field']] = $rr;
			$col[$this->field['Field']] = 'Valid';
  		}
  		if (is_array($field)) {
    		foreach ($field as $q=>$w) {
				if (isset($col[$q]))
					if ($col[$q] != 'Valid') 
						unset($field[$q]);
    		}
  		}
  		if (is_array($field)) {
    		foreach ($field as $q=>$w) {
      			if (! get_magic_quotes_gpc()) {
					$field[$q] = addslashes($w);
				} else {
					$field[$q] = $w;
				}
    		}
  		}
  		$this->field = $field;
  		if (empty($this->field[$this->identifier]) OR intval($this->field[$this->identifier]) == 0) {
			$this->InsertRecord();
  		} else {
    		$x = $this->InitiateQuery("SELECT `".$this->identifier."` 
				FROM `".$this->table."`
				WHERE `".$this->identifier."` = ".$this->field[$this->identifier]);
			$this->field = $field;
			if (empty($x[$this->identifier])) {
	  			$this->InsertRecord();
			} else {
   			 	$this->UpdateRecord();
			}
  		}
  		$this->query = $query;
  		$this->lastrow = $lastrow;
  		$this->thisrow = $thisrow;
		$this->PDO = NULL;
		$this->result = NULL;
		unset($this->result, $this->PDO);
  		$this->result = $result;
		$this->fetch = $fetch;
  		return $this->field[$this->identifier];
	}
/**
  * Update Record
  *
  * Change a stored record in a MySQL table.
  * @version 15th July 2011<br />
  * 3rd July 2009: Automated detection of field that can be set to NULL.
  * @since 26th June 2009
  * @private
  * @return void
  */
	private function UpdateRecord() {

		$this->LogCall('UpdateRecord($Null = array())');
  		$this->functionCalled = 'Update Record: ';
 		if (empty($this->identifier)) {
   			$this->SetError("The Table Identifier has not been set in MySQL class.");
   			$this->debug();
 		}
 		if (empty($this->table)) {
   			$this->SetError("The Table Name has not been set in MySQL class.");
   			$this->debug();
 		}
 		$this->OpenDatabase();
		$exists = false;
		$x = 0;
 		$this->ok = true;
 		if ($this->field[$this->identifier] == 0) {
   			$this->SetError("You can only modify an existing record.  Please 
   				select the record you wish to modify before attempting to modify data.");
   			if ($this->TestMode) 
				$this->debug();
 		}
 		if ($this->ok) {
   			$query = "SELECT * 
   				FROM `".$this->table."` 
   				WHERE `".$this->identifier."` = ".$this->field[$this->identifier];
   			$result = $this->PDO->query($query);
   			$row = $result->fetchall(PDO::FETCH_ASSOC);
			$result = NULL;
			$this->PDO = NULL;
			unset($result, $this->PDO);
			$field = $this->field;
			$this->InitiateQuery('SHOW COLUMNS FROM `'.$this->table.'`');
			$this->FieldDetails = array();
			while ($this->thisrow < $this->lastrow) {
				$rr = $this->RetrieveRow(true);
				$this->FieldDetails[$rr['Field']] = $rr;
				$col[$this->field['Field']] = 'Valid';
			}
			$row = $row[0];
			$this->OpenDatabase();
   			unset($exist);
   			unset($update);
			$this->field = $field;
			unset($field);
			$field = array();
   			foreach($row as $key => $value) {
     			if (($this->EscapePost($value) !== $this->EscapePost($this->field[@$key]))) {
       				@$exist .= "`".$key.'` = '.$this->EscapePost($value).'|,| ';
					$field[] = $key;
     			}
   			}
			if (count($field) === 1 AND in_array('RecordChange', $field)) {
				$field = array();
				unset($exist);
			}
   			if (! isset($exist)) {
    			$this->SetError("You made no changes to your record.  No action taken.", '9999');
				return;
   			}
   			if ($this->ok) {
				$update = '';
 				foreach($this->field as $key => $value) {
					if (isset($this->FieldDetails[$key])) {
						$e = explode('(', $this->FieldDetails[$key]['Type']);
						switch ($e[0]){
							case 'int':
								$update .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
								break;
							case 'tinyint':
								$update .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
								break;
							case 'smallint':
								$update .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
								break;
							case 'mediumint':
								$update .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
								break;
							case 'bigint':
								$update .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
								break;
							case 'bool':
								$update .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
								break;
							case 'double':
								$update .= "`".$key."` = ".floatval($this->EscapePost($value)).", ";
								break;
							case 'float':
								$update .= "`".$key."` = ".floatval($this->EscapePost($value)).", ";
								break;
							case 'decimal':
								$update .= "`".$key."` = ".floatval($this->EscapePost($value)).", ";
								break;
							default:
								$x = mb_strlen(strval($value));
								if($x == 0 AND $this->FieldDetails[$key]['Null'] == 'YES') {
									$update .= "`".$key."` = NULL, ";
								} else {
									$update .= "`".$key."` = '".$this->EscapePost($value)."', ";
								}
						}
					}
 				}
     			$date = date("Y-m-d H:i:s");
	 			$this->SaveChanges($exist, 'Update');
     			$x = mb_strlen($update);
     			$update = mb_substr($update, 0, $x - 2);
     			$this->LastQuery = $query = "UPDATE `".$this->table."` 
	 				SET ".$update." 
	 				WHERE `".$this->identifier."` = ".$this->field[$this->identifier];
     			if ($this->ok) {
					$result = NULL;
					unset($result);
       				$result = $this->PDO->exec($query);
       				if (intval($this->PDO->errorCode()) != 0) {
	     				$this->SetError("Update Error: <br />Query = ".$query, $this->PDO->errorCode());
         				if ($this->TestMode) 
							$this->debug();
       				} else {
						$this->SaveChanges('', 'Update');
					}
					$result = NULL;
					unset($result);
     			}
   			}
 		}
		return ;
	}
/** 
  * Escape Data
  * 
  * Escape the array for inclusion in a query.<br />
  * Simulates magic quotes being ON, regardless of magic_quotes_gpc status
  * @version 26th May 2011
  * @since 27th June 2009
  * @param mixed The data to be processed
  * @return mixed
  */
  	function EscapePost($post) {
 
   
		$this->LogCall('EscapePost($post = '.strval($post).')');
		if (is_array($post)) 
     		foreach($post as $q=>$w) {
				if (is_array($w)) {
	     			$this->EscapePost($w);
	   			} else {
					if (get_magic_quotes_gpc())
						$post[$q] = stripslashes($post[$q]);
         			$post[$q] = addslashes($post[$q]);
	   			}
     		}
   		if (is_string($post)) {
			if (get_magic_quotes_gpc())
				$post = stripslashes($post);
         	$post = addslashes($post);
  		}
		return $post;
 	} 
/** 
  * Clear Query Properties
  *
  * @public
  * @version 27th June 2009
  * @since 27th June 2009
  * @return void
  */
 	function ClearQuery() {
 
		$this->LogCall('ClearQuery()');
   		$this->query = '';
   		$this->Join = '';
   		$this->where = '';
  		$this->select = '';
   		$this->OrderBy = '';
   		$this->table = '';
   		$this->thisrow = 0;
   		$this->lastrow = 0;
		return ;
 	} 
/**
  * Load Array from Query
  *
  * Seed the LoadArray method with a query and identifying field.
  * @version 27th June 2009
  * @since 27th June 2009
  * @param string The identifying field for the array
  * @param string A new query, defaults to $this->query
  * @return array
  */
  	function LoadArrayFromQuery($field, $query = NULL){
	
		$this->LogCall('LoadArrayFromQuery($field = '.strval($field).', $query = '.strval($query).')');
		$this->functionCalled = 'Load Array From Query: ';
		$this->InitiateQuery($query);
		return $this->LoadArray($field);
	}
/**
  * Load Array 
  *
  * Load an array with the input of a query, using the field identified.
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string Identified field
  * @return array
  */
  	function LoadArray($field = NULL) {
		
		$this->LogCall('LoadArray($field = '.strval($field).')');
		$this->functionCalled = 'Load Array: ';
		$res  = array();
		if (empty($field))
			$field = $this->identifier;
		if (empty($field)) {
			$res = $this->fetch;
			unset($this->fetch);
			$this->fetch = array();
			return $res;
		} else {
			foreach($this->fetch as $q=>$x) {
				$res[$x[$field]] = $x;
			}
		}
		unset($this->fetch);
		$this->fetch = array();
		return $res;
	}
/**
  * Insert Record
  *
  * Insert a new record into a MySQL table.
  * @version 5th July 2009
  * @since 27th June 2009
  * @private
  * @return void
  */
	private function InsertRecord() {

		$this->LogCall('InsertRecord()');
  		$this->functionCalled = 'Insert Record: ';
 		if (empty($this->identifier)) {
   			$this->SetError("The Table Identifier has not been set in MySQL class.<br />");
   			$this->debug();
 		}
 		if (empty($this->table)) {
   			$this->SetError("The Table Name has not been set in MySQL_record.<br />");
   			$this->debug();
 		}
 		$this->OpenDatabase();
 		$this->ok = true;
		if (empty($this->field[$this->identifier]))
 			unset($this->field[$this->identifier]);
 		$insert = "";
 		foreach($this->field as $key => $value) {
			if (isset($this->FieldDetails[$key]['Type'])) {
				$e = explode('(', $this->FieldDetails[$key]['Type']);
				if (! empty($e[0])) {
					switch ($e[0]){
						case 'int':
							$insert .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
							break;
						case 'tinyint':
							$insert .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
							break;
						case 'smallint':
							$insert .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
							break;
						case 'mediumint':
							$insert .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
							break;
						case 'bigint':
							$insert .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
							break;
						case 'bool':
							$insert .= "`".$key."` = ".intval($this->EscapePost($value)).", ";
							break;
						case 'double':
							$insert .= "`".$key."` = ".floatval($this->EscapePost($value)).", ";
							break;
						case 'float':
							$insert .= "`".$key."` = ".floatval($this->EscapePost($value)).", ";
							break;
						case 'decimal':
							$insert .= "`".$key."` = ".floatval($this->EscapePost($value)).", ";
							break;
						default:
							$insert .= "`".$key."` = '".$this->EscapePost($value)."', ";
					}
				}
			} else {
				unset($this->field[$key]);
			}
 		}
 		$date = date("Y-m-d H:i:s");
 		$x = mb_strlen($insert);
 		$insert = mb_substr($insert, 0, $x - 2);
 		$this->LastQuery = $query = "INSERT INTO `".$this->table."` SET ".$insert;
 		$result = $this->PDO->exec($query);
 		if (intval($this->PDO->errorCode()) !== 0) {
  			$this->SetError("Insert Failure: ".print_r($this->PDO->errorInfo(), true)."<br />".$query, $this->PDO->errorCode());
  			if ($this->TestMode)
				switch ($this->errno) { 
					case 1062:
						return;
						break;
					default:
						$this->debug();
				}
 		} else {
  			$this->field[$this->identifier] = $this->PDO->lastInsertId();
  			$this->SaveChanges($query, 'Insert');
 		}
		$result = NULL;
		unset($result);
		return ;
	}
/**
  * Delete Record
  *
  * Delete a record from a record set.<br />
  * Requires the following variables to be set:<br />
  * field[$this->identifier] = the unique identifer of the record to set<br />
  * table name and table identitier<br />
  * Generates a copy of the data to the changes table before the record is deleted.<br />
  * <b>DO NOT USE THIS METHOD TO DELETE RECORDS FROM THE 'changes' TABLE</b>
  * @version 17th August 2010
  * @since 26th June 2009
  * @return void
  */
	function DeleteRecord() {

		$this->LogCall('DeleteRecord()');
  		$this->functionCalled = 'Delete Record: ';
 		if (empty($this->identifier)) {
   			$this->SetError("The Table Identifier has not been set in MySQL class.");
   			$this->debug();
 		}
 		if (empty($this->table)) {
   			$this->SetError("The Table Name has not been set in MySQL class.");
   			$this->debug();
 		}
		if (empty($this->field[$this->identifier])) {
			$this->SetError("The Field Identifier ".$this->identifier." has not been set.");
			$this->debug();
		}
 		$this->OpenDatabase();
 		$x = 0;
 		$this->ok = true;
   		$query = "SELECT * 
   			FROM `".$this->table."` 
   			WHERE `".$this->identifier."` = ".$this->field[$this->identifier];
   		$result = $this->PDO->query($query);
		if (! is_object($result)) {
			$this->SetError("Delete Record: ".print_r($this->PDO->errorInfo(), true)."<br />".$query, $this->PDO->errorCode());
			$this->debug();
		}
		$row = $result->fetchAll(PDO::FETCH_ASSOC);
		$count = count($row);
		$row = $row[0];
		$result = NULL;
		unset($result);
   		if ($count !== 1) 
			return ;
   		unset($exist);
		$exist = '';
   		foreach($row as $key => $value) 
			$exist .= strval($key).' = '.strval($value).'|,| '; 
   		$this->SaveChanges($exist, 'Delete');
 		$this->LastQuery = $query = "DELETE FROM `".$this->table."` 
 			WHERE `".$this->identifier."` = ".$this->field[$this->identifier]."
 			LIMIT 1";
 		if ($this->ok) {
  			$result = $this->PDO->exec($query);
  			if (intval( $this->PDO->errorCode()) !== 0) {
				$this->SetError("Delete Record: ".print_r($this->PDO->errorInfo(), true)."<br />".$query, $this->PDO->errorCode());
				$this->debug();
			} else {
				$this->SaveChanges('', 'Delete');
			}
 		}
		$result = NULL;
		$this->PDO = NULL;
 		unset($result, $this->PDO);
		return ;
	}
/**
  * Next Identifier
  *
  * Sets the next_id variable to reflect the new unique record number of table.
  * @version 27th June 2009
  * @since 27th June 2009
  * @param string The table name can be past to the function, or defaults to the existing object->table
  * @return integer  The Next Identifier
  */
	function NextIdentifier($table = "NO TABLE") {
  
		$this->LogCall('NextIdentifier($table = '.strval($table).')');
  		$this->functionCalled = 'Next Identifier: ';
   		if ($table != "NO TABLE") {
     		$this->table = $table;
   		} 
 		if (empty($this->table)) {
			$this->SetError("The Table Name has not been set in MySQL_record.<br />");
   			$this->debug();
 		}
  		$this->OpenDatabase();
  		$result = $this->PDO->query("SHOW TABLE STATUS LIKE '".$this->table."'");
  		$err = $this->PDO->errorCode();
  		if (intval($err) > 0) {
			$this->SetError("Next Identifier: ".print_r($this->PDO->errorInfo(), true), $err);
    		if ($this->TestMode) 
				$this->debug();
  		}
  		$row = $result->fetchAll(PDO::FETCH_ASSOC);
  		$this->next_id = $row[0]['Auto_increment'];
		$result = NULL;
		$this->PDO = NULL;
		unset($result, $this->PDO, $row);
  		return $this->next_id;
	}
/**
  * Prepare Query
  *
  * Initiates a query and returns the row indicated by thisrow<br />
  * <br />
  * sets the field array with the results (slashes striped from field) or<br />
  * sets error / errno with the problem.<br />
  * sets ok to indicate success<br />
  * sets query, thisrow, lastrow, result
  * @version 26th June 2009
  * @since 26th June 2009
  * @param string A new query to replace the internal query.
  * @return void
  */
	function PrepareQuery($query = "NO QUERY") {

		$this->LogCall('PrepareQuery($query = '.strval($query).')');
  		$this->FunctionCalled = 'Prepare Query: ';
  		if ($query === 'Build') {
    		$this->query = $this->BuildSelectQuery();
  		} elseif ($query !== "NO QUERY") {
   		 	$this->query = $query;
  		}
		$this->ClearQuery();
		$this->ClearError();
  		$this->OpenDatabase();
  		$this->result = $this->PDO->prepare($this->query);
  		$err = $this->PDO->errorCode();
  		if (intval($err) > 0) {
			$this->SetError("Prepare Query: ".print_r($this->PDO->errorInfo(), true),  $err);
			if ($this->TestMode) {
				switch ($this->errno) {
	  				case 1146:
						break;
					default:
						$this->debug();
				}
			}
  		}
		return ;
	}
/**
  * Execute Prepared Query
  *
  * @version 29th June 2009
  * @since 29th June 2009
  * @param array Prepared Query Values.
  * @return array Table record Set.
  */
  	function ExecutePreparedQuery($values){
	
		$this->LogCall('ExecutePreparedQuery($value = '.strval(print_r($value, true)).')');
  		$this->FunctionCalled = 'Execute Prepare Query: ';
		if (! is_array($value))
			return array();
		$this->result->execute($value);
		$err = $this->PDO->errorCode();
  		if (intval($err) > 0) {
			$this->SetError("Execute Prepared Query: ".print_r($this->PDO->errorInfo(), true),  $err);
			if ($this->TestMode) {
				switch ($this->errno) {
	  				case 1146:
						break;
					default:
						$this->debug();
				}
			}
  		}
		$this->fetch = $this->result->fetchAll(PDO::FETCH_ASSOC);
		$err = $this->PDO->errorCode();
  		if (intval($err) > 0) {
			$this->SetError("Execute Prepared Query: ".print_r($this->PDO->errorInfo(), true),  $err);
			if ($this->TestMode) {
				switch ($this->errno) {
	  				case 1146:
						break;
					default:
						$this->debug();
				}
			}
  		}
  		$this->lastrow = 0;
		$this->thisrow = 0;
  		if ($this->ok) {
    		$this->lastrow = count($this->fetch);
			if ($this->thisrow > $this->lastrow - 1) 
	  			$this->thisrow = $this->lastrow - 1;
			if ($this->thisrow < 0) 
	  			$this->thisrow = 0;
			if (empty($this->fetch[$this->thisrow]) AND $this->lastrow > 0) {
				$this->SetError("Prepared Query Execute: Failed to set the pointer in the query. Pointer: .".$this->thisrow.' in '.$this->lastrow);
      			if ($this->TestMode) 
	    			$this->debug();
			} else {
	 			if ($this->lastrow > 0) {
	  				foreach ($this->fetch[$this->thisrow] as $q=>$w) {
        				$this->field[$q] = stripslashes($w);
      				} 
	 			}
			}
  		}
		if (! is_array($this->field))
			$this->field = array();
  		return $this->field;
	}
/**
  * Close Prepared Query
  *
  * @version 29th June 2009
  * @since 29th June 2009
  * @return void
  */
  	function ClosePreparedQuery(){
	
		$this->LogCall('ClosePreparedQuery()');
		$this->PDO = NULL;
		$this->result = NULL;
		unset($this->PDO, $this->result, $this->fetch);
		return ;
	}
/**
  * Update Series
  *
  * Update a series of records from a table.
  * @version 29th June 2009<br />
  * @since 29th June 2009
  * @param string  an expression used to match records in MySQL to be updated.
  * @param array Field key as field name, value to be added to the field.
  * @param boolean Use the internal query to seed the series.
  * @return integer The number of records up-dated
  */ 
 	function UpDateSeries($where, $fields, $UseQuery = false) {

		$this->LogCall('UpDateSeries($where = '.strval($where).', $fields = '.strval($fields).', $UseQuery = '.strval(intval($UseQuery)).')');
  		$this->functionCalled = 'Delete Series: ';
   		if (empty($this->identifier)) {
  			$this->SetError("The Table Identifier has not been set in the MySQL class.");
     		if ($this->TestMode) 
				$this->debug();
   		}
   		if (empty($this->table)) {
     		$this->SetError("The Table Name has not been set in MySQL class.");
     		if ($this->TestMode) 
				$this->debug();
   		}
   		$query = 'SELECT * 
   			FROM `'.$this->table.'` 
   			WHERE '.$where;
		if ($UseQuery)
			$query = $this->query;
   		$this->InitiateQuery($query);
   		if (! $this->ok) {
     		if ($this->TestMode) 
				$this->debug();
   		}
   		$count = 0;
   		while ($this->thisrow < $this->lastrow) {
			$this->RetrieveRow(true);
			foreach($fields as $q=>$w){
				$this->field[$q] = $w;
			}
			$this->SaveRecord();
			if ($this->ok)
				$count++;
   		}
		return $count;
 	}
/**
  * Delete Series
  *
  * Delete a series of records from a table.
  * @version 20th July 2009
  * @since 29th June 2009
  * @param string an expression used to match records in MySQL to be deleted.
  * @param boolean Use the internal query to seed the series.
  * @return integer The number of records deleted.
  */ 
 	function DeleteSeries($where, $UseQuery = false) {

		$this->LogCall('DeleteSeries($where = '.strval($where).')');
  		$this->functionCalled = 'Delete Series: ';
   		if (empty($this->identifier)) {
     		$this->SetError("The Table Identifier has not been set in MySQL_record.");
     		if ($this->TestMode) 
				$this->debug();
   		}
   		if (empty($this->table)) {
     		$this->SetError("The Table Name has not been set in MySQL_record.");
     		if ($this->TestMode) 
				$this->debug();
   		}
   		$query = 'SELECT * 
   			FROM `'.$this->table.'` 
   			WHERE '.$where;
		if ($UseQuery)
			$query = $this->query;
   		$this->InitiateQuery($query);
   		if (! $this->ok) {
     		if ($this->TestMode) 
				$this->debug();
   		}
   		$count = $this->lastrow;
   		while ($this->lastrow > 0) {
     		$this->DeleteRecord();
	 		$this->InitiateQuery($query);  
   		}
   		$this->ExecuteQuery('OPTIMIZE TABLE `'.$this->table.'`');
		return $count;
 	}
/**
  * Purge Changes
  *
  * Purge the changes table up till the date specified in the var $date<br />
  * This function can be called by a CRON program.
  * @version 30th August 2011
  * @since 6th July 2009
  * @param date $date The date in form 'Y-m-d' that records in changes older than $date are to be deleted.<br />
  * defaults to 60 days ago<br />
  * eg: $date = date("Y-m-d", strtotime('-60 Days')); 
  * @return void
  */
 function PurgeChanges($date = 'NO DATE' ) {
   
		$this->LogCall('PurgeChanges($date = '.strval($date).')');
   		if ($this->PurgeChangesDone) 
     		return ;
   		$this->PurgeChangesDone = true;
   		$this->functionCalled = 'Purge Changes: ';
     	$tbl = array();
	 	$this->InitiateQuery("SELECT * 
	  		FROM `".PRE_NAME."PurgeData`");
	 	while ($this->thisrow < $this->lastrow) {
	   		$tbl[] = $this->RetrieveRow(true);
	 	}
		require_once LIBRARY_PATH.'class.cron.php';
		$cron = new CronManager();
	 	foreach($tbl as $q=>$w) {
			$cron->RecordCronLog('PurgeChanges Table '.$w['TableName'].' started.');
	   		// echo "Working on ".$w['TableName'];
	   		switch ($w['multiplier']) {
		 		case 'Nil':
		   			$m = 1;
		   			break;
		 		case 'kB':
		   			$m = 1024;
		   			break;
		 		case 'MB':
		   			$m = 1024 * 1024;
		   			break;
	   		}
	   		switch ($w['method']) {
	     		case 'Records':
		   			$this->InitiateQuery("SELECT `".$w['TableIdentifier']."`
		    			FROM `".$w['TableName']."`
						ORDER BY `".$w['TableIdentifier']."`");
		   			while ($this->lastrow > $m * $w['size']) {
			 			$this->ExecuteQuery("DELETE FROM `".$w['TableName']."` 
							ORDER BY `".$w['TableIdentifier']."` 
							LIMIT 1");
		     			$this->InitiateQuery("SELECT `".$w['TableIdentifier']."`
		      				FROM `".$w['TableName']."`
			  				ORDER BY `".$w['TableIdentifier']."`");
		   			}
		   			break;
		 		case 'Date':
           			$date = date("Y-m-d", strtotime('-'.$w['size'].' '.$w['multiplier']));
           			$this->InitiateQuery("SELECT * 
						FROM `".$w['TableName']."` 
						WHERE `".$w['RowDate']."` < '".$date."'");
           			$this->ExecuteQuery("DELETE FROM `".$w['TableName']."` 
						WHERE `".$w['RowDate']."` < '".$date."' 
						LIMIT ".$this->lastrow);
		   			break;
		 		case 'TableSize':
		   			$data = $this->InitiateQuery("SHOW TABLE STATUS LIKE '".$w['TableName']."'");
		   			while (($w['size'] * $m) < ($data['Data_length'] - $data['Data_free'])) {
		     			$this->ExecuteQuery("DELETE FROM `".$w['TableName']."`
			  				ORDER BY `".$w['TableIdentifier']."`
			  				LIMIT 1");
		     			$data = $this->InitiateQuery("SHOW TABLE STATUS LIKE '".$w['TableName']."'");
	       			}
		   			break;
	   		}
			$cron->RecordCronLog('PurgeChanges Table '.$w['TableName'].' completed.');
	 	}
		//Erase Log Files...
		$keep = 2;  //Number of days to keep the files.
		$f = @opendir(MYSQL_LOGPATH);
		while (($file = readdir($f)) !== false) {
			if (substr($file, 0, 3) === 'sql') {
				if (date('Y-m-d', strtotime(substr($file, 3, 10))) <= date('Y-m-d', strtotime('-'.$keep.' days'))) {
					@unlink(MYSQL_LOGPATH.$file);
				}
			}
		}
		closedir($f);
		$this->OptimiseTables();
		return ;
 	}
/**
  * Optimise Tables
  *
  * Optimise the tables in this database.<br />
  * An ideal candidate for a CRON job on a weekly basis.<br />
  * This method is called by the PurgeDatabase method.
  * @version 6th July 2009
  * @since 6th July 2009
  * @return void
  */
 	function OptimiseTables() {
 
		$this->LogCall('OptimiseTables()');
  		$this->functionCalled = 'Optimise Tables: ';
   		$this->InitiateQuery('SHOW TABLE STATUS');
   		$tables = array();
   		while ($this->thisrow < $this->lastrow) {
     		$this->RetrieveRow();
     		if ($this->field['Data_free'] > 0) {
				$tables[$this->thisrow] = $this->field['Name'];
			}
			$this->thisrow++;
   		}
   		foreach ($tables as $q=>$w) {
     		$this->ExecuteQuery('OPTIMIZE TABLE '.$w);
   		}
 	} 
/**
  * Build Join String
  *
  * Requires the following format in the array $this->Join keys.<br />
  * The final character in the key must be one of the following letters<br />
  * Y = The type of join:  LEFT, RIGHT, CENTER<br />
  * F = Comma separated field list for join testing<br />
  * T = Table Name to join too.
  * @version 8th July 2009
  * @since 8th July 2009
  * @return string The Join clause of an SQL query.
  */
	function BuildJoinString() {
 
		$this->LogCall('BuildJoinString()');
		ksort($this->Join);
		unset($y, $jo, $f, $j, $t);
		foreach($this->Join as $q=>$w) {
			$k = substr($q, 0, -1);
			if ($y !== $k) {
				if (! empty($f)) 
					$jo .= "\n ".$j." JOIN ".$t."\n ON ".$this->TestforTableNames($f[0]).' = '.$this->TestforTableNames($f[1]);
				$y = $k;
				unset($f, $t, $j);
			}
			$g = substr($q, strlen($q) - 1, 1);
			switch ($g) {
				case "F":
					$f = explode(',', $w);
					break;
				case "J":
					$j = $w;
					break;
				case "T":
					$t = $this->AddTablePrefix($w);
					break;
				default:
					exit('Unable to parse the JOIN string in the SQL query correctly.');
			}
		}
		if (! empty($f)) {
			$jo .= "\n ".$j." JOIN ".$t."\n ON ".$this->TestforTableNames($f[0]).' = '.$this->TestforTableNames($f[1]);
		} else {
			$jo .= "\n ";
		}
		return $jo;
	}
/**
  * Reset SQL Variables
  *
  * Reset the PDO variables to NULL or Empty.
  * @version 29th November 2009
  * @since 29th November 2009
  * @return void
  */
  	function ResetSQL(){
	
		$this->LogCall('ResetSQL()');
		$this->fetch = array();
		$this->query = '';
		$this->result = NULL;
		$this->PDO = NULL;
		$this->field = array();
		$a = memory_get_usage(true);
		return;
	}
/**
  * Load a Record
  *
  * @version 15th July 2011
  * @since 15th July 2011
  * @param string Table Name
  * @param string Table Identifier (MUST BE UNIQUE)
  * @param mixed The value of the table identifier to return.
  * @return array The record from the table.
  */
  	function LoadRecord($table, $identifier, $id) {
	
		$this->LogCall('LoadRecord($table = '.strval($table).', $identifier = '.strval($identifier).', $id = '.strval($id).')');
		return $this->InitiateQuery("SELECT * FROM `".$table."` WHERE `".$identifier."` = '".$id."'");
	}
/**
  * Collation Management
  *
  * Change database, tables and fields to a standard collation and character set.<br />
  * Default is CHARACTER SET utf8 COLLATE utf8_bin <br />
  * Define CHARACTERSET and/or COLLATE to change these values.
  * @version 31st August 2011
  * @since 30th August 2011
  * @return void
  */
  	function CollationManagement(){
	
		$this->LogCall("CollationManagement()");
		$x = 0;
		$this->InitiateQuery("SHOW TABLES");
		while($this->thisrow < $this->lastrow){
			$row = $this->RetrieveRow(true);
			$key = key($row);
			$table[$x++] = $row[$key];
		}
		$this->ExecuteQuery("ALTER DATABASE `".$this->DataBaseName."` CHARACTER SET ".$this->CharacterSet." COLLATE ".$this->Collate);
		foreach($table as $w){
			$this->ExecuteQuery("ALTER TABLE `".$w."` DEFAULT CHARACTER SET ".$this->CharacterSet." COLLATE ".$this->Collate);
			$row = $this->LoadArrayFromQuery('Field', "SHOW FULL COLUMNS FROM `".$w."`");
			foreach($row as $e=>$r){
				if (strlen($r['Collation']) > 0) {
					$query = "ALTER TABLE `".$w."` CHANGE `".$e."` `".$e."` ".$r['Type']." CHARACTER SET ".$this->CharacterSet." COLLATE ".$this->Collate." ";
					if ($r['Null'] == 'No') {
						$query .= "NOT NULL ";
					} else {
						$query .= 'NULL ';
					}
					if (strlen($r['Default']) > 0) {
						$query .= "DEFAULT '".$r['Default']."' ";
					}
					$this->ExecuteQuery($query);
				}
			}
		}
		return ;
	}
/**
  * Set Identifier Name
  *
  * @version 11th December 2011
  * @since 11th December 2011
  * @return void
  */
  	function SetIdentifierName(){
	
		$this->LogCall("SetIdentifierName()");
		$this->OpenDataBase();
		$query = "SELECT `k`.`column_name`
			FROM `information_schema`.`table_constraints` AS `t`
			JOIN `information_schema`.`key_column_usage` AS `k`
			USING(`constraint_name`, `table_schema`, `table_name`)
			WHERE `t`.`constraint_type` = 'PRIMARY KEY'
				AND `t`.`table_schema` = '".$this->DataBaseName."'
				AND `t`.`table_name` = '".$this->table."'";
		$row = $this->InitiateQuery($query);
		$this->identifier = $row['column_name'];
		return ;
	}
/**
  * Flush Tables
  *
  * The FLUSH statement clears or reloads various internal caches used by MySQL.
  * @version 27th December 2011
  * @since 27th December 2011
  * @return void
  */
	function FlushTables(){
	
		$this->LogCall('FlushTables()');
		$query = 'FLUSH TABLES';
		$this->ExecuteQuery($query);
		return ;
	}
} 
/**
  * Kill SQL Object
  *
  * @since 3rd October 2011
  * @version 3rd October 2011
  * @param object SQL 
  * @return void
  */
function KillSQL($sql){

	$sql = NULL;
	return ;
}
?>
