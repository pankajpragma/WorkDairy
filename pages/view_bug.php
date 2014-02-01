<?php 
$f_bug_id = gpc_get_int( 'id' );
bug_ensure_exists( $f_bug_id );
$tpl_bug = bug_get( $f_bug_id, true );
$g_project_override = $tpl_bug->project_id;
access_ensure_bug_level( VIEWER, $f_bug_id );
$tpl_bug_due_date = date( config_get( 'normal_date_format' ), $tpl_bug->due_date );
$t_user_id = auth_get_current_user_id();

# get the bugnote data
$t_bugnote_order = current_user_get_pref( 'bugnote_order' );
$t_bugnotes = bugnote_get_all_visible_bugnotes( $f_bug_id, $t_bugnote_order, 0, $t_user_id );
$t_normal_date_format = config_get( 'normal_date_format' );
#precache users
$t_bugnote_users = array();
foreach($t_bugnotes as $t_bugnote) {
	$t_bugnote_users[] = $t_bugnote->reporter_id;
}
user_cache_array_rows( $t_bugnote_users );

$num_notes = count( $t_bugnotes );
 
?> 
<table style="width:100%" >
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category" style="width: 115px;" >
			Reporter
		</td>
		<td>
			<?php echo print_user_with_subject( $tpl_bug->reporter_id, $f_bug_id );?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category">
			Assigned To
		</td> 
		<td>
			<?php echo print_user_with_subject( $tpl_bug->handler_id, $f_bug_id );?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category" >
			Project Name
		</td> 
		<td>
			<?php echo string_display_line( project_get_name( $tpl_bug->project_id ) );?>
		</td>
	</tr>
	
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category" >
			Due Date
		</td> 
		<td>
			<?php echo $tpl_bug_due_date;?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category">
			Priority
		</td> 
		<td>
			<?php echo string_display_line( get_enum_element( 'priority', $tpl_bug->priority ) );?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category" >
			Summary
		</td> 
		<td>
			<?php echo string_display_line_links( $tpl_bug->summary );?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category" >
			Description
			
		</td> 
		<td>
			<?php echo string_display_links( $tpl_bug->description );?>
		</td>
	</tr>
	<tr <?php echo helper_alternate_class() ?>>
		<td  class="category" >
			Attachments
		</td> 
		<td>
			<?php print_bug_attachments_list( $f_bug_id );?>
		</td>
	</tr>

	<?php 
		for ( $i=0; $i < $num_notes; $i++ ) {
		$t_bugnote = $t_bugnotes[$i];	
	?>
		<tr <?php echo helper_alternate_class() ?>>
			<td  class="category" >
				<?php
					echo print_user( $t_bugnote->reporter_id );
				?><Br>
				<span class="small"><?php echo date( $t_normal_date_format, $t_bugnote->date_submitted ); ?></span>
			</td> 
			<td>
				<?php echo string_display_links( $t_bugnote->note );?>
			</td>
		</tr>	
	<?php 
	   }
	 ?>
</table>