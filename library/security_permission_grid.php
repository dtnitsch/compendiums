<?php
function build_permission_grid($section = 'section',$group = 'group', $permission = 'permission') {

	if(!empty($_POST[$section])) {
		foreach($_POST[$section] as $section_id) {
			if(!empty($_POST[$group][$section_id])) {
				unset($_POST[$group][$section_id]);
			}
			if(!empty($_POST[$permission][$section_id])) {
				unset($_POST[$permission][$section_id]);
			}
		}
	}

	if(!empty($_POST[$group])) {
		foreach($_POST[$group] as $sid => $r1) {
			foreach($r1 as $gid => $r2) {
				if(!empty($_POST[$permission][$sid][$gid])) {
					unset($_POST[$permission][$sid][$gid]);
				}
			}
		}
	}

	$arr = array();
	$used = array();
	if(!empty($_POST[$permission])) {
		foreach($_POST[$permission] as $sid => $r1) {
			foreach($r1 as $gid => $r2) {
				foreach($r2 as $permission_id) {
					$section_id = (!empty($_POST[$section][$sid]) ? $sid : 0);
					$group_id = (!empty($_POST[$group][$sid][$gid]) ? $gid : 0);
					$used[$section][$section_id] = 1;
					$used[$group][$group_id] = 1;
					$arr[] = [$section_id , $group_id , $permission_id];
				}
			}
		}
	}

	if(!empty($_POST[$group])) {
		foreach($_POST[$group] as $sid => $r1) {
			foreach($r1 as $group_id) {
				$section_id = (!empty($_POST[$section][$sid]) ? $sid : 0);
				$used[$section][$section_id] = 1;
				if(empty($used[$group][$group_id])) {
					$arr[] = [$section_id , $group_id ,0];
				}
			}
		}
	}

	if(!empty($_POST[$section])) {
		foreach($_POST[$section] as $section_id) {
			if(empty($used[$section][$section_id])) {
				$arr[] = [$section_id,0,0];
			}
		}
	}

	unset($used);
	return $arr;
}