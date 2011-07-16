<?php
/******************************************************************
***            Configuration File                               ***
******************************************************************/
$version['config.php'] = '15th July 2011';

ob_start("gzhandler");
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();
ini_set("log_errors", 1);  // 
ini_set("display_errors", 1); // set to 0 in production site
ini_set("error_log", "/path-to/php-error.log");


@define('TEST_MODE', true);
date_default_timezone_set('Australia/Canberra');
set_time_limit(60);

//  MYSQL Access Variables
@define("MYSQL_HOST", "127.0.0.1");  // some versions of mysql fail to connect on localhost.  
@define("MYSQL_USER", "mysqluser");
@define("MYSQL_PASS", "123qwe");
@define("MYSQL_DB", "mysql_db");
@define("MYSQL_LOGPATH", "/path-to/sql_log/");
@define('SKIP_CHANGES', 'PDF,VisitLog,BugCatcher,CronLog,CronList'); // comma separated list of table names that do not store changes.
@define("CRON_ON", false);


?>