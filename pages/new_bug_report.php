<?php 
access_ensure_project_level( config_get('report_bug_threshold' ) );
$t_bug_data = new BugData;
$t_bug_data->project_id             = gpc_get_int( 'project_id', 0 );
$t_bug_data->reporter_id            = auth_get_current_user_id();
$t_bug_data->handler_id             = gpc_get_int( 'handler_id', 0 );
$t_bug_data->category_id            = gpc_get_int( 'category_id', 0 );
$t_bug_data->priority               = gpc_get_int( 'priority', config_get( 'default_bug_priority' ) );
$t_bug_data->summary                = trim( gpc_get_string( 'summary' ) );
$t_bug_data->description            = gpc_get_string( 'description' );
$t_bug_data->due_date               = gpc_get_string( 'due_date', '');
if ( is_blank ( $t_bug_data->due_date ) ) {
	$t_bug_data->due_date = date_get_null();
}
# Allow plugins to pre-process bug data
$t_bug_data = event_signal( 'EVENT_REPORT_BUG_DATA', $t_bug_data );
# Ensure that resolved bugs have a handler
if ( $t_bug_data->handler_id == NO_USER && $t_bug_data->status >= config_get( 'bug_resolved_status_threshold' ) ) {
	$t_bug_data->handler_id = auth_get_current_user_id();
}
# Create the bug
$t_bug_id = $t_bug_data->create();
# Mark the added issue as visited so that it appears on the last visited list.
last_visited_issue( $t_bug_id );
helper_call_custom_function( 'issue_create_notify', array( $t_bug_id ) );
# Allow plugins to post-process bug data with the new bug ID
event_signal( 'EVENT_REPORT_BUG', array( $t_bug_data, $t_bug_id ) );
email_new_bug( $t_bug_id );
?>