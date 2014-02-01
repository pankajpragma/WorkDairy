<?php
require_once( 'bug_api.php' );
require_once( 'bugnote_api.php' );
$t_bug_id     = gpc_get_int( 'bug_id' );
$f_bug_notetext  = gpc_get_string( 'note' );
$f_action	= 'UP_STATUS';
$f_status = gpc_get_int( 'status' );
if($f_status == ASSIGNED)
{
	$f_action	= 'ASSIGN';
} 
else if($f_status == '')
{
	$f_action	= 'NOTE';
} 
bug_ensure_exists( $t_bug_id );
$t_bug = bug_get( $t_bug_id, true );

if( $t_bug->project_id != helper_get_current_project() ) {
	$g_project_override = $t_bug->project_id;
	config_flush_cache(); # flush the config cache so that configs are refetched
}
$t_status = $t_bug->status;
$t_failed_ids = array();
switch ( $f_action ) {
	case 'UP_STATUS':		
		$t_project = bug_get_field( $t_bug_id, 'project_id' );
		if ( access_has_bug_level( access_get_status_threshold( $f_status, $t_project ), $t_bug_id ) ) {
			if ( TRUE == bug_check_workflow($t_status, $f_status ) ) {								
				# Add bugnote if supplied
				if ( !is_blank( $f_bug_notetext ) ) {
					bugnote_add( $t_bug_id, $f_bug_notetext, null );
				}				
				bug_set_field( $t_bug_id, 'status', $f_status );				
				helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
				echo "1";
			} else {
				echo lang_get( 'bug_actiongroup_status' );
			}
		} else {
			echo lang_get( 'bug_actiongroup_access' );
		}
		break; 
	case 'ASSIGN':
		$f_assign = gpc_get_int( 'new_handler_id' );
		if ( ON == config_get( 'auto_set_status_to_assigned' ) ) {
			$t_assign_status = config_get( 'bug_assigned_status' );
		} else {
			$t_assign_status = $t_status;
		}
		# check that new handler has rights to handle the issue, and
		#  that current user has rights to assign the issue
		$t_threshold = access_get_status_threshold( $t_assign_status, bug_get_field( $t_bug_id, 'project_id' ) );
		if ( access_has_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $t_bug_id ) ) {
			if ( access_has_bug_level( config_get( 'handle_bug_threshold' ), $t_bug_id, $f_assign ) ) {
				if ( bug_check_workflow( $t_status, $t_assign_status ) ) {
					
					bug_assign( $t_bug_id, $f_assign, $f_bug_notetext, $f_bug_noteprivate );
					$t_due_date  = gpc_get_string( 'due_date2', null ); 
					if( $t_due_date !== null) {
						if ( is_blank ( $t_due_date ) ) {
							$t_bug->due_date = 1;
						} else {
							$t_bug->due_date = strtotime( $t_due_date );
						}						
					}	
					$t_bug->status = ASSIGNED;
					$t_bug->update( true);
						 
					helper_call_custom_function( 'issue_update_notify', array( $t_bug_id ) );
					echo "1";
				} else {
					echo lang_get( 'bug_actiongroup_status' );
				}
			} else {
				echo lang_get( 'bug_actiongroup_handler' );
			}
		} else {
			echo lang_get( 'bug_actiongroup_access' );
		}
		break;
	case 'NOTE':
		if ( bug_is_readonly( $t_bug_id ) ) {
			error_parameters( $t_bug_id );
			trigger_error( ERROR_BUG_READ_ONLY_ACTION_DENIED, ERROR );
		}
		access_ensure_bug_level( config_get( 'add_bugnote_threshold' ), $t_bug_id );
	 
		$t_bugnote_id = bugnote_add( $t_bug_id, $f_bug_notetext, '0:00', false, BUGNOTE );
	    if ( !$t_bugnote_id ) {
	        error_parameters( lang_get( 'bugnote' ) );
	        trigger_error( ERROR_EMPTY_FIELD, ERROR );
	    }	
	    else {
	    	echo "2";
	    }
	    break;
	default:
		trigger_error( ERROR_GENERIC, ERROR );
}
// Bug Action Event
event_signal( 'EVENT_BUG_ACTION', array( $f_action, $t_bug_id ) );