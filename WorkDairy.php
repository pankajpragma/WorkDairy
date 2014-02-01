<?php
/*
   Copyright 2013 Pankaj U. Dadure

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

   Notes: Based on the Work Dairy plugin by Pankaj:
*/
class WorkDairyPlugin extends MantisPlugin {

	function register() {
		$this->name = 'Work Dairy';
		$this->description = 'Work dairy plugin show your weekly target issues according to project and due date, It also execute some functionality using ajax for quick operation. It contain timer functionality for each issue. You can see here screencast http://screencast.com/t/Cobp0qTfn';
		$this->page = 'config_page';

		$this->version = '1.0.0';
		$this->requires = array(
			'MantisCore' => '1.2.0'
		);
		$this->author = 'Pankaj Dadure';
		$this->contact = 'pankaj.pragma@gmail.com';
		$this->url = '';
	}
	function hooks() {		
		return array(
			'EVENT_MENU_MAIN'      => 'showreport_menu',
			'EVENT_LAYOUT_RESOURCES'  => 'print_head_resources',
		);
	}

	function config() {
		return array();
	}

	function init() {
		$t_path = config_get_global('plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
		set_include_path(get_include_path() . PATH_SEPARATOR . $t_path);
	}
 
	function showreport_menu() {		 
			return array( '<a href="' . plugin_page( 'my_dairy' ) . '">' . plugin_lang_get( 'title' ) . '</a>', );
	}
	
	/**
	 * Create the resource link to load the required library.
	 */
	function print_head_resources() {
		return '<script type="text/javascript" src="' . plugin_file( 'script.js' ) . '"></script>'.
			'<link rel="stylesheet" type="text/css" href="' . plugin_file( 'style.css' ) . '" />';
	}
	

} # class end
?>
