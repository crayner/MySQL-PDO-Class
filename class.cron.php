<?php
/**
 * CRON Manager
 * 
 * Designed as a library of methods to manage a continuous 1 minute call to a CRON table
 * built as a Table in MySQL Database.
 * @package craig
 * @version 26th March 2011
 * @since 26th December 2005
 * @copyright Craig Rayner 2005 - 2011<br />
 *  Information Record Sysem for Registered Training Organisation: Australia.<br />
 *  Copyright (C) 2004-2011  Craig A. Rayner<br />
 *  <br />
 *  This program is free software: you can redistribute it and/or modify<br />
 *  it under the terms of the GNU General Public License as published by<br />
 *  the Free Software Foundation, either version 3 of the License, or<br />
 *  (at your option) any later version.<br />
 *  <br />
 *  This program is distributed in the hope that it will be useful,<br />
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of<br />
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the<br />
 *  GNU General Public License for more details.<br />
 *  <br />
 *  You should have received a copy of the GNU General Public License<br />
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.*/
/**
 * CRON Manager
 * 
 * Designed as a library of methods to manage a continuous 1 minute call to a CRON table
 * built as a Table in MySQL Database.
 * @package craig
 * @version 26th March 2011
 * @since 26th December 2005
 * @author Craig Rayner
 * @copyright Craig Rayner 2005 - 2011
 */
class CronManager {
/**
 * Version: Current Class Version Number
 * @access public
 * @var string
 */
var $version = '26th March 2011';
/**
  * VTest Mode
  *
  * @var boolean
  */
var $TestMode = false;
/**
 * Cron Manager Constructor
 * @version 16th January 2009<br>
 * 16th January 2009: Inhibit loginf of CREATE TABLE queries.<br>
 * 6th November 2007: Added log of Cron Manager Initiation.
 * @since 26th December 2005
 * @return void
 */
 function CronManager() {
 
   		global $version;
		if (TEST_MODE)
			$this->TestMode =  true;
		$this->LogCall('CronManager()');   
   		if (@$version['class.cron.php'] == $this->version)
   			return ;
   		$version['class.cron.php'] = $this->version;
   		$db = new mysql_record();
		$db->DoNotLog[] = array();
		$query = "CREATE TABLE IF NOT EXISTS `CronList` (
    `id` int(11) unsigned zerofill NOT NULL auto_increment,
    `url` varchar(100) NOT NULL default '',
    `interval` int(11) NOT NULL default '0',
    `measure` enum('Seconds','Minutes','Hours','Days','Weeks','Months','Years') NOT NULL default 'Seconds',
    `CallNext` datetime default NULL,
    `SSL` enum('Yes','No') NOT NULL default 'No',
    `priority` int(2) NOT NULL default '10',
    `repeat` enum('Yes','No') NOT NULL default 'Yes',
     PRIMARY KEY  (`id`),
     KEY `url` (`url`),
     FULLTEXT KEY `url_2` (`url`)
     ) TYPE=MyISAM COMMENT='CRON Table Used by library/CronTable.php and library/class.cron.php'";
   		$db->ExecuteQuery($query);
		$db->DoNotLog[] = $query = "CREATE TABLE IF NOT EXISTS `CronLog` (
    `id` INT( 11 ) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT ,
    `file` VARCHAR( 255 ) NULL ,
    `ActionDate` DATETIME NULL ,
	`comment` VARCHAR( 255 ) NULL ,
    PRIMARY KEY ( `id` )
    ) TYPE = MYISAM ";
   		$db->ExecuteQuery($query);
	
		unset($db);
 	}
/**
 * Record a Completed CRON Job
 *
 * This method must be called within the page to be effective.  You can call this method 
 * during or at the end of the process as many times as is necessary to validate operation 
 * of the process.
 * @version 26th December 2005
 * @since 26th December 2005
 * @param string $comment  A Comment to store in the Log.
 * @return void
 */
 	function RecordCronLog($comment){
 
		$this->LogCall('RecordCronLog($comment = '.strval($comment).')');   
   		$db = new mysql_record();

   		$db->field['file'] = $_SERVER['PHP_SELF'];
   		$db->field['comment'] = $comment;
   		$db->field['ActionDate'] = date("Y-m-d H:i:s");
  		$db->SaveRecord('CronLog', 'id');
   		unset($db);
 	} 
/**
 * Test the minute for another job to do
 *
 * This process needs to be called every minute by a CRON Job or a Scheduled task in Windows.
 * @version 26th March 2011<br />
 * 13th March 2011: Test if the file exists for the URL.<br />
 * 17th February 2011: Added trap for blank url.<br />
 * 17th September 2007: Added test for a log record and terminate program on positive.
 * 24th July 2006: Add Call to file into log.
 * @since 26th December 2005
 * @return void
 */
 function AnotherMinute(){

		$this->LogCall('AnotherMinute()');   
   		$db = new mysql_record();
		$db->InitiateQuery("SELECT * 
			FROM `CronList`
			WHERE `executing` = ".true);
		if ($this->TestMode)
			printAnObject($db->field);
		if (intval($db->lastrow) > 0)
			if ($db->field['StartExecuting'] > date('Y-m-d H:i:s', strtotime('-10 minutes'))) {
				return ;
			} else {
				$db->field['StartExecuting'] = '0000-00-00 00:00:00';
				$db->field['executing'] = false;
				$db->SaveRecord('CronList', 'id');
			}
   		$rr = $db->InitiateQuery("SELECT * 
    		FROM `CronList`
    		WHERE `CallNext` <= '".date('Y-m-d H:i')."'
    		ORDER BY `priority`,
				`CallNext`
    		LIMIT 1");
		if ($this->TestMode)
			printAnObject($rr);
		if (empty($rr['url']) AND $rr['id'] > 0) {
			$db->table = 'CronList';
			$db->identifier = 'id';
			$db->DeleteRecord();
			unset($db);
			return ;
		}
   		if ($db->lastrow === 1) {
     		$url = SITE_URL;
			$file = SITE_PATH;
     		if ($rr['SSL'] === 'Yes') {
       			$url = SSL_URL;
				$file = SSL_PATH;
     		}
     		$url .= $rr['url'];
			$db->InitiateQuery("SELECT `file` 
				FROM `CronLog`
				WHERE `ActionDate` >= '".date('Y-m-d H:i:s', strtotime('-40 seconds'))."'
					AND `file` = '/".$rr['url']."'");
			if ($db->lastrow > 0) {
				$x = '';
				if ($this->TestMode)
					$x = 'Exit due to file already running. 1';
				exit($x); // Exit due to file already running.
			}
			if ($db->field['executing'] AND strtotime('-10 Minutes') < $rr['StartExecuting'])  {
				if ($this->TestMode)
					$x = 'Exit due to file already running. 2';
				exit($x); // Exit due to file already running.
			}
			$PartUrl = explode("?", $rr['url']);
			if (! is_file($file.$PartUrl[0]))
				exit('The URL does not exist. ['.$file.$rr['url'].']');
			$rr['executing'] = true;
			$rr['StartExecuting'] = date('Y-m-d H:i:s');
     		$db->field = $rr;
     		$db->SaveRecord('CronList', 'id');
			if ($this->TestMode)
				printAnObject($db);
	 		$this->RecordCronLog('Start: The file '.$url.' was called by AnotherMinute in class.cron.php');
			if ($this->TestMode)
				printAnObject($url);
	 		header("Location: ".$url);
	 		exit();
   		}
   		unset($db);
		return ;
 	}
/**
  * Log of the Methods Called.
  *
  * @version 4th August 2007
  * @since 4th August 2007
  * @param string Method Name
  * @return void
  */
  	function LogCall($method) {
	
		if (! $this->TestMode) 
			return;
		global $version;
		$x = explode(' ', microtime());
		$version['class.cron.php '.strval(number_format($x[1] + mb_substr($x[0], 1), 6, '.', ''))] = $method;
		return;
	}
/**
  * Complete the Call
  *
  * @version 27th January 2011
  * @since 25th May 2009
  * @return void
  */
  	function CompleteCall(){
	
		$this->LogCall('CompleteCall()');   
   		$db = new mysql_record();
		$s = "/".SITE_SUBDIRECTORY;
		$r = '';
		if (! empty($_GET)) {
			foreach($_GET as $q=>$w) {
				$r .= '&'.$q.'='.$w; 
			}
			$r = "?".mb_substr($r, 1);
		}
		$rr = $db->InitiateQuery("SELECT * 
			FROM `CronList` 
			WHERE `url` LIKE '%".basename($_SERVER['PHP_SELF']).$r."%'");
		if ($db->lastrow == 1) {
			$rr['executing'] = false;
			$rr['StartExecuting'] = '0000-00-00 00:00:00';
			while (date('Y-m-d H:i:s', strtotime($rr['CallNext'])) < date('Y-m-d H:i:s')) {
				$rr['CallNext'] = date('Y-m-d H:i:s', strtotime($rr['CallNext'].'+'. $rr['interval'].' '.$rr['measure']));
			}
			$db->field = $rr;
				$db->SaveRecord('CronList', 'id');
			if ($rr['repeat'] === 'No') {
				$db->field = $rr;
				$db->table = 'CronList';
				$db->identifier = 'id';
				$db->DeleteRecord();
			}
		}
		return ;
	}
} #The End of the Class.
/**
  * Cron Table Function
  *
  * @version 17th February 2011
  * @since 13th January 2008
  * @return void
  */
function CronTable() {
	
	if (basename($_SERVER['PHP_SELF']) !== 'class.cron.php') {
		return ; 
	} else {
 		require_once '../../secure/config.php';
	}
	if (CLOSE_SITE) 
		return ;
	sleep(rand(1,12));
	sleep(rand(1,12));
	$cron = new CronManager();
	if ($_GET['DEBUG'] == 1)
		$cron->TestMode = true;

	$cron->AnotherMinute();
	return ;
}
/**
  * Call the Class Table Check
  *
  * @version 13th January 2008
  */
CronTable();
?>