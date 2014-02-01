<?php 
require_once( 'core.php' ); 

require_once( 'workdairy_api.php' ); 
/**
 * requires current_user_api
 */
require_once( 'current_user_api.php' );
/**
 * requires bug_api
 */
 require_once( 'bug_api.php' );
/**
 * requires string_api
 */
require_once( 'string_api.php' );
/**
 * requires date_api
 */
require_once( 'date_api.php' );

auth_ensure_user_authenticated();

if ( count( current_user_get_accessible_projects() ) == 1) {
	$t_project_ids = current_user_get_accessible_projects();
	$t_project_id = (int) $t_project_ids[0];
	if ( count( current_user_get_accessible_subprojects( $t_project_id ) ) == 0 ) {
		$t_ref_urlencoded = string_url( $f_ref );
		print_header_redirect( "set_project.php?project_id=$t_project_id&ref=$t_ref_urlencoded", true);
	}
}

html_page_top( plugin_lang_get( 'title' ) ); 
 
$t_user_id = auth_get_current_user_id();
$t_access_level = user_get_field( $t_user_id, 'access_level' );
$t_user_table = db_get_table( 'mantis_user_table' );
$t_bug_table = db_get_table( 'mantis_bug_table' );
$t_project_table = db_get_table( 'mantis_project_table' );

$sql = "SELECT id, realname FROM $t_user_table WHERE enabled='1' AND access_level >= ".UPDATER;
if(!in_array($t_access_level, Array(MANAGER, ADMINISTRATOR)))
{
	$sql .= " AND id='".$t_user_id."'";
}
$result_pull_users = db_query_bound( $sql );
$result_pull = Array();
while ( $t_row2 = db_fetch_array( $result_pull_users ) ) 
	 $result_pull[] = $t_row2;
?>

<div class="myworkdairy" >
<br>
 <div class="Timer" ></div>
<div class="section_dairy" >
<table cellspacing="1" class="width100 tblworkdairy  ">
<tbody><tr>
	<td colspan="2" class="form-title">
	<a  class="subtle">Weekly Target</a>&nbsp; 
	<input type="button" name="btn_quick_task" id="btn_quick_task" value="Quick task"  class="button-small topopup1" 
		title="Add new task"
	 >
	&nbsp; 
	<input type="button" name="btn_todays_updates" id="btn_todays_updates" value="Today Updates"  class="button-small topopup2" title="View today's updates" >
	&nbsp;&nbsp;&nbsp;
	<a href="javascript:void(0);" class="active_tab selected_tab" data-value="c"  title="Current week assigned issues"  >Current</a> 
	&nbsp;
	<a  href="javascript:void(0);" class="active_tab" data-value="n"  title="View next week issues"  >Next Week</a>
</td>
</tr>
<?php 
$week = Array('current_week' , 'next_week');
foreach ($week as $k1 => $v1) { 
?>
<?php 
foreach ($result_pull as $k2 => $t_row2) {

$query_pull_bug = "SELECT mbt.id,  mbt.summary, mpt.name, mbt.due_date, mbt.handler_id,mbt.project_id   FROM 
$t_bug_table mbt
LEFT JOIN $t_project_table  mpt ON mbt.project_id = mpt.id
WHERE mbt.status='".ASSIGNED."' AND mbt.handler_id='".$t_row2['id']."'  ";

if($v1 == 'current_week')
{
	$due_date = date('Y-m-d',strtotime("next sunday"));	
	$query_pull_bug .= "  AND due_date <=  UNIX_TIMESTAMP('".$due_date."')  ";
}
else if($v1 == 'next_week')
{
	$due_date1 = date('Y-m-d',strtotime("next sunday"));	
	list($y, $m, $d) = explode("-", $due_date1);
	$due_date2 = date("Y-m-d", mktime(0, 0, 0, $m, $d+7, $y));	
	$query_pull_bug .= "  AND due_date > UNIX_TIMESTAMP('".$due_date1."') AND  due_date <= UNIX_TIMESTAMP('".$due_date2."') ";	
}
$query_pull_bug .= " ORDER BY mbt.due_date, mbt.date_submitted ASC";
$result_pull_bugs = db_query_bound( $query_pull_bug );
$myprojects = $result_data = Array();
$cnt_issue = 1;
while ( $t_row = db_fetch_array( $result_pull_bugs ) ) {
	$result_data[] = $t_row;
	if(isset($myprojects[$t_row['project_id']]))
	{
		$myprojects[$t_row['project_id']][1] += 1; 
		continue;
	}
		
   $myprojects[$t_row['project_id']] = Array($t_row['name'], $cnt_issue);
}

?>
<tr bgcolor="#c2dfff" class="<?php echo $v1; ?>" >
<td>
<?php if(in_array($t_access_level, Array(MANAGER, ADMINISTRATOR)))
{ ?>
	<ul style="margin: 0px;" >
	    	<li><span class="Collapsable">Task assigned: <?php echo $t_row2['realname']?></span>
<?php } ?>	    	
	    	<?php 
	    		if(empty($myprojects))
	    		{
	    			echo "<p class='no_records' >No task assigned!</p>";
					continue;
	    		}
	    	?>
	    	<table class="width100" style="margin-top:10px;<?php if($t_user_id != $t_row2['id']) {?>display:none;<?php } ?>" >
	    		<tr class="td_backgrnd">
	    			<td class="workdairy_td1" style="padding-left:70px;" >Task</td>
	    			<td style="text-align: left;" >Due</td>
	    			<td style="text-align: center;" >Action</td>
	    		</tr>
	    		<tr><td colspan="3">
	    	
	    	<ul>
	 		<?php 	
	foreach($myprojects as $key => $value)
	{
		?>
		 <li class="li_marg"  ><span class="Collapsable"><?php echo $value[0]; ?>&nbsp;<span class="cnt_count" >(<?php echo $value[1]; ?>)</span></span>
			 <ul style="margin-left: 0px; padding-left:24px;">
	<?php 
	foreach ( $result_data as $k => $t_row ) {
	 
		if($t_row['project_id'] != $key)
			continue;
	 
		$due_date = date( config_get( 'short_date_format' ), $t_row['due_date'] );
		$start_time = 1; 
		$start_date = '';
		$t_timereport_table = plugin_table('data', 'TimeTracking');
		if(file_exists(dirname(dirname(dirname(__FILE__))).'\TimeTracking'))
		{
			$sql = "SELECT expenditure_date FROM $t_timereport_table WHERE bug_id='".$t_row['id']."' AND timestamp='0000-00-00 00:00:00' AND hours=0  LIMIT 0, 1";
			$t_results = db_query_bound($sql);
			
			if(db_num_rows($t_results))
			{
				$t_row_timer = db_fetch_array( $t_results );	
				$start_time = 0; 
				$start_date = date('D M d Y H:i:s O', strtotime($t_row_timer['expenditure_date']));
			}
		}
	?> 
	<li class="li_bug_<?php echo $t_row['id']; ?>" style="list-style-type: square;" >
	            	<div class="col1" style="width:270px;" >            		
						<?php echo bug_format_summary( $t_row['id'], SUMMARY_FIELD ) ; ?>
						<span id="counter_bug_<?php echo $t_row['id']; ?>" class="timer_countdown" ></span>
	            	</div>
	            	<div class="col2 <?php echo is_due_crossed($due_date) ?>">
	            		<?php echo $due_date; ?>
	            	</div>
	            	<div class="col3">
	            		<a href="javascript:void(0);"  title="Mark Closed" class="link_operation"   data-value1='<?php echo $t_row['id']; ?>' data-value2='<?php echo CLOSED; ?>' >C</a>&nbsp;
	            		<a href="javascript:void(0);" title="Mark Feedback" class="link_operation"  data-value1='<?php echo $t_row['id']; ?>' data-value2='<?php echo FEEDBACK; ?>' >F</a>&nbsp;
	            		<a href="javascript:void(0);"  title="View Details" class="link_view" data-value='<?php echo $t_row['id']; ?>'   >V</a>&nbsp;
	            		<a href="javascript:void(0);"  title="Add Note" class="link_operation"  data-value1='<?php echo $t_row['id']; ?>' data-value2='0'  >N</a>&nbsp;
	            		<a href="bug_update_page.php?bug_id=<?php echo $t_row['id']; ?>" title="Edit Issue" >E</a>&nbsp;						
						<a href="javascript:void(0);" title="Start Timer"  class="timer_operation <?php if(!$start_time) { ?> hide_me <?php } ?> "  data-value1='<?php echo $t_row['id']; ?>' data-value2='start'
						id="sttimer_bug_<?php echo $t_row['id']; ?>" 
						><img src="plugin_file.php?file=WorkDairy/timer_start.png"  title="Start Timer" /> </a> 					 
						<a href="javascript:void(0);" title="Stop Timer"  class="timer_operation <?php if($start_time) { ?> hide_me <?php } ?> "  data-value1='<?php echo $t_row['id']; ?>' data-value2='stop' 
						id="sptimer_bug_<?php echo $t_row['id']; ?>" 
						><img src="plugin_file.php?file=WorkDairy/timer_stop.png"  title="Stop Timer" /></a> 
						<?php if(!$start_time) { ?>
							<script>
							setInterval(function() {
								start = new Date('<?php echo $start_date; ?>');
								$('#counter_bug_<?php echo $t_row['id']; ?>').text(get_elapsed_time_string((new Date - start) / 1000));
							}, 1000);
							</script>
						<?php } ?>
	            	</div>
	            </li>
	<?php 		
	  
	}
	?>
	</ul>
	
	<?php  
	}
	?>
	
	       
		       
	    </ul>
	    </td>
	    
	    </tr></table>
	    <?php if(in_array($t_access_level, Array(MANAGER, ADMINISTRATOR)))
{ ?>
	    </li>
	    
	     
	</ul>
	<?php } ?>
 	</td>
</tr>
<?php } ?>
<?php } ?>

</tbody></table>

</div>
 
<div class="section_dairy" >
<table cellspacing="1" class="width100 tblworkdairy  ">
<tbody><tr>
	<td colspan="2" class="form-title">
	<a  class="subtle">Issue Reported By Me</a> 
</td>
</tr>
<?php 
$where = " reporter_id='".$t_user_id."' AND status = '".ASSIGNED."' ";
$sql = "SELECT id  FROM $t_bug_table
		WHERE $where
		order by project_id ASC ";
$result_pull_bugs = db_query_bound( $sql );
?>
<tr bgcolor="#c2dfff"  >
<td>
	 
	    	<?php 
	    		if(!db_num_rows($result_pull_bugs))
	    		{
	    			echo "<p class='no_records' >No issue is reported by you!</p>";					 
	    		}
	    		else { 
	    	?>
	    	<table class="width100" style="margin-top:10px;" >
	    		<tr class="td_backgrnd">
	    			<td class="workdairy_td1" >Task</td>
	    			<td style="text-align: center;" >Due</td>
	    			<td style="text-align: center;" >Action</td>
	    		</tr>
	    		<tr><td colspan="3">
	    	
	    	<ul>
	 		<?php 	
			$cnt = 1;
			$last_project = 0;
			while ( $t_row = db_fetch_array( $result_pull_bugs ) ) {
			$t_bug = bug_get( $t_row['id'], true );
		?>
		<?php if($last_project != $t_bug->project_id) { ?>
			<?php if(!empty($last_project)) { ?>
					</ul></li>
			<?php } ?>
			<li class="li_marg"  >
		 	<span class="Collapsable"><?php echo string_display_line( project_get_name( $t_bug->project_id ) );
					 $last_project = $t_bug->project_id;
					 $cnt = 1;
					 ?>
					 <?php echo get_project_count($t_bug->project_id , $where); ?>
					 </span>
			 <ul  class="ul_css1" >		 
			<?php } ?>
	<?php  
	 
		$due_date = date( config_get( 'short_date_format' ), $t_bug->due_date );
	?> 
	<li class="li_bug_<?php echo $t_bug->id; ?>" >
            	<div class="col1" style="width: 330px;" >           		
					<?php echo bug_format_summary( $t_bug->id, SUMMARY_FIELD ) ; ?>
            	</div>
            	<div class="col2 <?php echo is_due_crossed($due_date) ?>">
            		<?php echo $due_date; ?>
            	</div>
            	<div class="col3">
            		<a href="javascript:void(0);"  title="Mark Closed" class="link_operation"   data-value1='<?php echo $t_bug->id; ?>' data-value2='<?php echo CLOSED; ?>' >C</a>&nbsp;
            		<a href="javascript:void(0);" title="Mark Feedback" class="link_operation"  data-value1='<?php echo $t_bug->id; ?>' data-value2='<?php echo FEEDBACK; ?>' >F</a>&nbsp;
            		<a href="javascript:void(0);"  title="View Details" class="link_view" data-value='<?php echo $t_bug->id; ?>'   >V</a>&nbsp;
            		<a href="javascript:void(0);"  title="Add Note" class="link_operation"  data-value1='<?php echo $t_row['id']; ?>' data-value2='0'  >N</a>&nbsp;
            		<a href="bug_update_page.php?bug_id=<?php echo $t_bug->id; ?>" title="Edit Issue" >E</a>&nbsp;	
            	</div>
            </li>
	<?php 		
	  
	}
	?>
	</ul></li>
	</ul>
	
	<?php  
	 }
	?>
	
	       
	 
	    </td>
	    
	    </tr></table>
	   
 	</td>
</tr>

</tbody></table>

</div>
 <div class="section_dairy" >
<table cellspacing="1" class="width100 tblworkdairy  ">
<tbody><tr>
	<td colspan="2" class="form-title">
	<a  class="subtle">Awaiting Feedback</a>&nbsp; 
</td>
</tr>
<?php 
$where = "  status = '".FEEDBACK."' ";
if(!in_array($t_access_level, Array(MANAGER, ADMINISTRATOR)))
	$where .= " AND reporter_id='".$t_user_id."'  ";
	
$sql = "SELECT id  FROM $t_bug_table
		WHERE  $where
		order by project_id ASC ";
$result_pull_bugs = db_query_bound( $sql );
?>
<tr bgcolor="#c2dfff"  >
<td>
	 
	    	<?php 
	    		if(!db_num_rows($result_pull_bugs))
	    		{
	    			echo "<p class='no_records' >No issue is awaiting for feedback!</p>";					 
	    		}
	    		else { 
	    	?>
	    	<table class="width100" style="margin-top:10px;" >
	    		<tr class="td_backgrnd">
	    			<td class="workdairy_td1" >Task</td>
	    			<td style="text-align: center;" >Due</td>
	    			<td style="text-align: center;" >Action</td>
	    		</tr>
	    		<tr><td colspan="3">
	    	
	    	<ul>
	 		<?php 	
			$cnt = 1;
			$last_project = 0;
			while ( $t_row = db_fetch_array( $result_pull_bugs ) ) {
			$t_bug = bug_get( $t_row['id'], true );
		?>
		<?php if($last_project != $t_bug->project_id) { ?>
			<?php if(!empty($last_project)) { ?>
					</ul></li>
			<?php } ?>
			<li class="li_marg"  >
		 	<span class="Collapsable"><?php echo string_display_line( project_get_name( $t_bug->project_id ) );
					 $last_project = $t_bug->project_id;
					 $cnt = 1;
					 ?>
					 <?php echo get_project_count($t_bug->project_id , $where); ?>
					 </span>
			<ul  class="ul_css1" >	 
			 
			<?php } ?>
			
		 
	<?php  
	 
		$due_date = date( config_get( 'short_date_format' ), $t_bug->due_date );
	?> 
	<li class="li_bug_<?php echo $t_bug->id; ?>" >
            	<div class="col1" style="width: 330px;" >           		
					<?php echo bug_format_summary( $t_bug->id, SUMMARY_FIELD ) ; ?>
            	</div>
            	<div class="col2 <?php echo is_due_crossed($due_date) ?>">
            		<?php echo $due_date; ?>
            	</div>
            	<div class="col3">
            		<a href="javascript:void(0);"  title="Mark Closed" class="link_operation"   data-value1='<?php echo $t_bug->id; ?>' data-value2='<?php echo CLOSED; ?>' >C</a>&nbsp;
            		<a href="javascript:void(0);" title="Mark Assigned" class="link_operation"  data-value1='<?php echo $t_bug->id; ?>' data-value2='<?php echo ASSIGNED; ?>'  data-value3='<?php echo $t_bug->handler_id; ?>'  >A</a>&nbsp;
            		<a href="javascript:void(0);"  title="View Details" class="link_view" data-value='<?php echo $t_bug->id; ?>'   >V</a>&nbsp;
            		<a href="javascript:void(0);"  title="Add Note" class="link_operation"  data-value1='<?php echo $t_row['id']; ?>' data-value2='0'  >N</a>&nbsp;
            		<a href="bug_update_page.php?bug_id=<?php echo $t_bug->id; ?>" title="Edit Issue" >E</a>&nbsp;	
            	</div>
            </li>
	<?php 		
	  
	}
	?>
	</ul></li>
	</ul>
	
	<?php  
	 }
	?>
	
	       
	 
	    </td>
	    
	    </tr></table>
	   
 	</td>
</tr>
</tbody></table>
</div>
<?php //if(in_array($t_access_level, Array(MANAGER, ADMINISTRATOR))) { ?>
<div class="section_dairy" >
<table cellspacing="1" class="width100    ">
<tbody><tr>
	<td colspan="2" class="form-title">
	<a  class="subtle">Unassigned</a>&nbsp; 
</td>
</tr>
<?php 
$where = " handler_id='0'  "; 
if(!in_array($t_access_level, Array(MANAGER, ADMINISTRATOR)))
	$where .= " AND reporter_id='".$t_user_id."'  ";
	
$sql = "SELECT id  FROM  $t_bug_table
		WHERE $where
		order by project_id ASC ";
$result_pull_bugs = db_query_bound( $sql );
?>
<tr bgcolor="#c2dfff"  >
<td>
	 
	    	<?php 
	    		if(!db_num_rows($result_pull_bugs))
	    		{
	    			echo "<p class='no_records' >No issue unassigned!</p>";					 
	    		}
	    		else { 
	    	?>
	    	<table class="width100" style="margin-top:10px;" >
	    		<tr class="td_backgrnd">
	    			<td class="workdairy_td1" >Task</td>
	    			<td style="text-align: center;" >Due</td>
	    			<td style="text-align: center;" >Action</td>
	    		</tr>
	    		<tr><td colspan="3">
	    	
	    	<ul>
	 		<?php 	
			$cnt = 1;
			$last_project = 0;
			while ( $t_row = db_fetch_array( $result_pull_bugs ) ) {
			$t_bug = bug_get( $t_row['id'], true );
		?>
		<?php if($last_project != $t_bug->project_id) { ?>
			<?php if(!empty($last_project)) { ?>
					</ul></li>
			<?php } ?>
			<li class="li_marg"  >
		 	<span class="Collapsable"><?php echo string_display_line( project_get_name( $t_bug->project_id ) );
					 $last_project = $t_bug->project_id;
					 $cnt = 1;
					 ?>
					  <?php echo get_project_count($t_bug->project_id , $where); ?>
					 </span>
			<ul  class="ul_css1" >	 
			 
			<?php } ?>
			
		 
	<?php  
	 
		$due_date = date( config_get( 'short_date_format' ), $t_bug->due_date );
	?> 
	<li class="li_bug_<?php echo $t_bug->id; ?>" >
            	<div class="col1" style="width: 330px;" >           		
					<?php echo bug_format_summary( $t_bug->id, SUMMARY_FIELD ) ; ?>
            	</div>
            	<div class="col2 <?php echo is_due_crossed($due_date) ?>">
            		<?php echo $due_date; ?>
            	</div>
            	<div class="col3">
            		<a href="javascript:void(0);"  title="Mark Closed" class="link_operation"   data-value1='<?php echo $t_bug->id; ?>' data-value2='<?php echo CLOSED; ?>' >C</a>&nbsp;
            		<a href="javascript:void(0);" title="Mark Assigned" class="link_operation"  data-value1='<?php echo $t_bug->id; ?>' data-value2='<?php echo ASSIGNED; ?>' >A</a>&nbsp;
            		<a href="javascript:void(0);"  title="View Details" class="link_view" data-value='<?php echo $t_bug->id; ?>'   >V</a>&nbsp;
            		<a href="javascript:void(0);"  title="Add Note" class="link_operation"  data-value1='<?php echo $t_row['id']; ?>' data-value2='0'  >N</a>&nbsp;
            		<a href="bug_update_page.php?bug_id=<?php echo $t_bug->id; ?>" title="Edit Issue" >E</a>&nbsp;	
            	</div>
            </li>
	<?php 		
	  
	}
	?>
	</ul></li>
	</ul>
	
	<?php  
	 }
	?>
	
	       
	 
	    </td>
	    
	    </tr></table>
	   
 	</td>
</tr>
</tbody></table>
</div>
<?php //} ?>
    <div id="toPopup1" class="toPopup" style="min-width:830px;" >
        <div class="close"></div>
       	<span class="ecs_tooltip">Press Esc to close <span class="arrow"></span></span>
		<div id="popup_content"> <!--your content start-->
            <table>
            	<tr <?php echo helper_alternate_class() ?>>
            		<td class="category"><span class="required">*</span><?php echo lang_get( 'choose_project' ) ?></td>
            		<td>            			
            			<select name="project_id">
							<?php print_project_option_list( ALL_PROJECTS, false, null, true ) ?>
						</select>
            		</td>
            	</tr>
            	<tr <?php echo helper_alternate_class() ?>>
					<td class="category" width="30%">
						<?php echo config_get( 'allow_no_category' ) ? '' : '<span class="required">*</span>'; print_documentation_link( 'category' ) ?>
					</td>
					<td width="70%">						 
						<select <?php echo helper_get_tab_index() ?> name="category_id">
							<?php
								print_category_option_list('1');
							?>
						</select>
					</td>
				</tr>
            	<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
						<?php print_documentation_link( 'priority' ) ?>
					</td>
					<td>
						<select <?php echo helper_get_tab_index() ?> name="priority">
							<?php print_enum_string_option_list( 'priority', $f_priority ) ?>
						</select>
					</td>
				</tr>
					<?php
					  $t_date_to_display = date( config_get( 'calendar_date_format' ), strtotime(date('Y-m-d H:i')));	 
					?>
					<tr <?php echo helper_alternate_class() ?>>
						<td class="category">
							<span class="required">*</span><?php print_documentation_link( 'due_date' ) ?>
						</td>
						<td>
						<?php
						    print "<input ".helper_get_tab_index()." type=\"text\" id=\"due_date\" name=\"due_date\" size=\"20\" maxlength=\"16\" value=\"".$t_date_to_display."\" />";
							date_print_calendar();
						?>
						</td>
					</tr>
			 
            	 <tr <?php echo helper_alternate_class() ?>>
					<td class="category">
						<?php echo lang_get( 'assign_to' ) ?>
					</td>
					<td>
						<select <?php echo helper_get_tab_index() ?> name="handler_id">
							<option value="0" selected="selected"></option>
							<?php print_assign_to_option_list( $f_handler_id ) ?>
						</select>
					</td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
						<span class="required">*</span><?php print_documentation_link( 'summary' ) ?>
					</td>
					<td>
						<input <?php echo helper_get_tab_index() ?> type="text" name="summary" size="105" maxlength="128" value="<?php echo string_attribute( $f_summary ) ?>" />
					</td>
				</tr>
				<tr <?php echo helper_alternate_class() ?>>
					<td class="category">
						<span class="required">*</span><?php print_documentation_link( 'description' ) ?>
					</td>
					<td>
						<textarea <?php echo helper_get_tab_index() ?> name="description" cols="80" rows="10"><?php echo string_textarea( $f_description ) ?></textarea>
					</td>
				</tr>            	 
            	<tr>
            		<td colspan="2" style="text-align: right;" >
            		<input type="button" name="btn_submit_quick_task" id="btn_submit_quick_task" value="Submit"  class="button-small"  >
            		</td>
            	</tr>
            </table> 
        </div> <!--your content end-->

    </div> <!--toPopup end-->

 
     <div id="toPopup3" class="toPopup" style="min-width: 650px;" >
        <div class="close"></div>
       	<span class="ecs_tooltip">Press Esc to close <span class="arrow"></span></span>
		<div id="popup_content"> <!--your content start-->
             Loading....
        </div> <!--your content end-->
    </div> <!--toPopup end-->
    
    <div id="toPopup4" class="toPopup" style="min-width: 540px;" >
        <div class="close"></div>
       	<span class="ecs_tooltip">Press Esc to close <span class="arrow"></span></span>
		<div id="popup_content"> <!--your content start-->
			<table cellspacing="1" class="width100" >
				<tr><td id="status_title"  class="form-title" colspan="2"  >Add Note</td></tr>
				 <tr <?php echo helper_alternate_class() ?> id="assigned_me1" >
					<td class="category">
						<span class="required">*</span><?php echo lang_get( 'assign_to' ) ?>
					</td>
					<td>
						<select <?php echo helper_get_tab_index() ?> name="new_handler_id" id="new_handler_id" >
							<option value="0" selected="selected"></option>
							<?php print_assign_to_option_list( $f_handler_id ) ?>
						</select>
					</td>
				</tr>
				<tr <?php echo helper_alternate_class() ?> id="assigned_me2"  >
						<td class="category">
							<span class="required">*</span><?php print_documentation_link( 'due_date' ) ?>
						</td>
						<td>
						<?php
						    print "<input ".helper_get_tab_index()." type=\"text\" id=\"due_date2\" name=\"due_date2\" size=\"20\" maxlength=\"16\" value=\"".$t_date_to_display."\" />";
							date_print_calendar('trigger2');
						?>
						</td>
					</tr>
				 <tr <?php echo helper_alternate_class() ?>>
					<td class="category">Note:</td>
					<td>
						<textarea id="note"  name="note" style="width:400px;height: 100px;" ></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" style="text-align: right;" >
						 <input type="hidden" name="bug_id" id="bug_id" value="" />
						 <input type="hidden" name="status" id="status" value="" />		 
           				 <input type="button"  value="Submit" id="btn_submit_operation" class="button-small" >	
					</td>
				</tr>
			</table>            
        </div> <!--your content end-->
    </div> <!--toPopup end-->
    
    
	<div class="loader"></div>
   	<div id="backgroundPopup"></div>
 
 


</div>
 
    
<?php 
date_finish_calendar( 'due_date', 'trigger' );
date_finish_calendar( 'due_date2', 'trigger2' );
html_page_bottom();
?>
