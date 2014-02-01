<?php 
 $t_bug_table = db_get_table( 'mantis_bug_table' );
$t_user_id = auth_get_current_user_id(); 
$current_date = date('Y-m-d H:i:s');
$sql = "SELECT id  FROM  $t_bug_table
		WHERE last_updated >= UNIX_TIMESTAMP('".$current_date."')
		AND (status = '".FEEDBACK."' OR status = '".CLOSED."')AND handler_id='".$t_user_id."'
		order by project_id ASC ";
$result_pull_bugs = db_query_bound( $sql );
if(!db_num_rows($result_pull_bugs))
{
	echo "<p>No updates available for today.</p>";
	exit;	
} 
?>
<table class="daily_updates" >
<?php 
	$cnt = 1;
	$last_project = 0;
	while ( $t_row = db_fetch_array( $result_pull_bugs ) ) {
	$t_bug = bug_get( $t_row['id'], true );
	 
?>
	<?php if($last_project != $t_bug->project_id) { ?>
	<tr>
		<td>
			 <b><?php echo string_display_line( project_get_name( $t_bug->project_id ) );
			 $last_project = $t_bug->project_id;
			 $cnt = 1;
			 ?></b>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td>
			<?php echo $cnt; $cnt++; ?>)&nbsp;
			<b><?php echo string_display_links( $t_bug->summary);?></b>- 
			<span class="description_content" ><?php echo substr(string_display_links( $t_bug->description ), 0, 200);?></span><br>
			<font style="color:red;" >- Completed</font>
		</td>
	</tr>
<?php } ?>
</table>
<style>
.daily_updates td,  .daily_updates span {
    font-family: Arial;	 					
}
.description_content{
color:gray;
}
</style>