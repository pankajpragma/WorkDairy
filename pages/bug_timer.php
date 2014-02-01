<?php
require_once( 'bug_api.php' ); 
$t_bug_id     = gpc_get_int( 'bug_id' );
$operation  = gpc_get_string( 'operation' );
$t_timereport_table = plugin_table('data', 'TimeTracking');
bug_ensure_exists( $t_bug_id );
$t_bug = bug_get( $t_bug_id, true );
$t_user_id = $t_bug->handler_id;
 
if(!file_exists(dirname(dirname(dirname(__FILE__))).'\TimeTracking'))
{
echo "Timer plugin is not installed, Please install it from here https://github.com/mantisbt-plugins/timetracking";
exit;
}
 
if($operation == 'start')
{
	$sql = "SELECT id FROM $t_timereport_table WHERE user='$t_user_id' AND timestamp='0000-00-00 00:00:00' AND  hours=0 LIMIT 0, 1";
	$results = db_query_bound($sql);
	if(db_num_rows($results))
	{
		echo "Already timer is one for another issue, Please stop timer and try again!";
		exit;
	}
	$sql = "SELECT id FROM $t_timereport_table WHERE bug_id='$t_bug_id' AND timestamp='0000-00-00 00:00:00' AND  hours=0 LIMIT 0, 1";
	$results = db_query_bound($sql);
	if(!db_num_rows($results))
	{
		$now = '0000-00-00 00:00:00';
		$expend = date("Y-m-d G:i:s");
		$t_time_value = 0;
		$t_time_info = '';
		# insert record	 
	   $query = "INSERT INTO $t_timereport_table ( user, bug_id, expenditure_date, hours, timestamp, info ) 
		  VALUES ( '$t_user_id', '$t_bug_id', '$expend', '$t_time_value', '$now', '$t_time_info')";
	   if(!db_query($query)){
		  trigger_error( ERROR_DB_QUERY_FAILED, ERROR );
	   }
	   else {
		echo "1";
		exit;
	   }
	}
	else{
		echo "Timer already started";
		exit;
	}
}
else if($operation == 'stop')
{
	$sql = "SELECT id, expenditure_date FROM $t_timereport_table WHERE bug_id='$t_bug_id' AND timestamp='0000-00-00 00:00:00' AND hours=0  LIMIT 0, 1";
	$results = db_query_bound($sql);
	if(db_num_rows($results))
	{
		$t_row = db_fetch_array( $results );		 
		$now = date("Y-m-d G:i:s");
		$expend = date("Y-m-d").' 00:00:00'; 		 
		$t_time_value = round(abs(strtotime($now) - strtotime($t_row['expenditure_date'])) / 60,2);
		if($t_time_value < 1)
		{
			echo "Timer should be great than one minute";
			exit;
		}
		$t_time_value = doubleval($t_time_value / 60);
		
		$t_time_info = $t_bug->summary;
		# UPDDATE record	   
	    $query = "UPDATE $t_timereport_table 
		SET `expenditure_date` = '$expend' , `hours` = '$t_time_value' , `timestamp` = '$now' , `info` = '$t_time_info'
		WHERE id='".$t_row['id']."'";
	    if(!db_query($query)){
		  trigger_error( ERROR_DB_QUERY_FAILED, ERROR );
	    }
	    else {
		  echo "1";
		  exit;
	   }
	}
	else{
		echo "Timer is not started yet, Please try again!";
		exit;
	}
}
echo "Please try again, unable to add record";