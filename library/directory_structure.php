<?php

function directory_list($dir,$ignore=array()) { 
	$output = directory_list_recursive($dir,$ignore);
	asort($output);
	return $output; 
} 

function directory_list_recursive($dir,$ignore) { 
	$output = array();

	$current_dir = scandir($dir);
	foreach($current_dir as $row) {
		if(in_array($row, array(".",".."))) { continue; } # don't show "." or ".."
		if(substr($row,0,1) == '.') { continue; } # hide hidden files

		// $ignore = false;
		// foreach($ignore as $ign) {
		// 	if(strpos($row,$ign) !== false) {
		// 		$ignore = true;
		// 		break;
		// 	}
		// }
		// if($ignore == true) { continue; }

		if(is_dir($dir . '/' . $row)) {
			$output[$row] = directory_list_recursive($dir . '/' . $row,$ignore);
		} else {
			$output[] = $row; 
		}
	}
	return $output; 
} 

function select_files_and_folders($arr,$start_path = '/',$output = '') {
	$cnt = 0;
	foreach($arr as $folder => $file) {
		if(is_array($file)) {
			$output .= "</optgroup>";
			$output = select_files_and_folders($file,str_replace('//','/',$start_path.'/'.$folder),$output);
		} else {
			if($cnt++ == 0) { $output .= "<optgroup label='". $start_path ."'>"; }
			$output .= '<option value="'. $start_path ."/". $file .'">'. $file .'</option>';
		}
	}
	return str_replace('//','/',$output);
}

function get_contents_from_directory($dir,$type='files',$show_hidden=false) {
	if(is_dir($dir)) {
		$directories = array();
		$files = array();
		$symbolic_links = array();

		if($handle = opendir($dir)) {
		    while(($file = readdir($handle)) !== false) {
		    	if(substr($file,0,1) == '.') { continue; }
		        if($file != "." && $file != "..") {
		            if(is_dir($dir.$file)) { 
						$directories[$dir] = $file;
					} else if(is_file($dir.$file)) {
						$files[$dir] = $file;
					} else if(is_link($dir.$file)) {
						$symbolic_links[$dir] = $file;
					}
		        }
		    }

		    closedir($handle);

			if($type == 'files' || $type == '') { return $files; }
			else if($type == 'directories') { return $directories; }
			else if($type == 'symbolic links') { return $symbolic_links; }
			else { return array('files'=>$files,'directories'=>$directories,'symbolic_links'=>$symbolic_links); }
		}
	} else {
		return false;
	}
}


function display_files_and_folders($arr,$start_path = '/') {
	$cnt = 0;
	foreach($arr as $folder => $file) {
		if(is_array($file)) {
			display_files_and_folders($file,str_replace('//','/',$start_path.'/'.$folder));
		} else {
			if($cnt++ == 0) { echo "<br><br><strong>". $start_path ."</strong>"; }
			echo "<br>". $file;		
		}
	}
}
