<?php
function is_due_crossed($due)
{
	$current_date = date('Y-m-d');
	if($due < $current_date)
	{
		return "reddue";
	}
}

function get_project_count($project_id, $where)
{
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$sql = "SELECT count(id) as cnt_records  FROM $t_bug_table
		WHERE $where AND project_id='".$project_id."'
		group by project_id  ";
	
	$result_pull_bugs = db_fetch_array(db_query_bound( $sql ));
	 
	return '<span class="cnt_count" >('.$result_pull_bugs['cnt_records'].")</span>";
}

?>