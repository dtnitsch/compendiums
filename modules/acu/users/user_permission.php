<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("admin_users_permissions")) { back_redirect(); }

post_queue($module_name,'modules/acu/users/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/users/permissions/');
}

##################################################
#	DB Queries
##################################################
library("users.php");
library("security_functions.php");

$full_permissions_res = get_full_permissions_list($id);

##################################################
#	Pre-Content
##################################################
$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = get_user_by_id($id);
}

add_css("security.css");
add_js("forms_advanced.js");
add_js("security.js");

library("functions.php",$GLOBALS["root_path"] ."modules/acu/users/");

##################################################
#	Content
##################################################
?>
	<h2 class='users'>Edit User Permissions: <?php echo $info["firstname"]." ".$info["lastname"]; ?></h2>
  
  <div class='content_container'>

	<?= user_navigation($id,"permissions") ?>

	<?= dump_messages() ?>

	<form method="post" action="">
<?php

	$res = get_full_permissions_list($id);

	$output = '';
	$perms = array();
	$sections = array();
	$groups = array();
	$permissions = array();
	$full_permissions = array();
	$type_prefix = "has";
	while($row = db_fetch_row($res)) {
		$full_permissions[] = $row;
		if(!is_null($row['override_section'])) {
			$type_prefix = "override";
		}
	}

	foreach($full_permissions as $row) {
		$perms[$row['section_id']][$row['group_id']][$row['id']] = $row;
		if(!empty($row[$type_prefix .'_section'])) {
			$sections[$row[$type_prefix .'_section']] = 1;
		}
		if(!empty($row[$type_prefix .'_group'])) {
			$groups[$row[$type_prefix .'_group']] = 1;
		}
		if(!empty($row[$type_prefix .'_permission'])) {
			$permissions[$row[$type_prefix .'_permission']] = 1;
		}
	}

	foreach($perms as $section_id => $tmp1) {
		$tmp = current(current($tmp1));
		#$checked = (!empty($info['section'][$section_id]) ? " checked" : '');
		$schecked = (!empty($sections[$section_id]) ? " checked" : '');
		$output .= "
		<div class='section_header'>
			<label for='s". $section_id ."'>
				<input type='checkbox' name='section[". $section_id ."]' id='s". $section_id ."' value='". $section_id ."'". $schecked ." onclick='check_all(this);'> ". $tmp['section'] ."
			</label>
		</div>
		<div class='section_body' id='s". $section_id ."_body'>
		";
		foreach($tmp1 as $group_id => $tmp2) {
			$tmp = current($tmp2);
			$gchecked = (!empty($groups[$group_id]) ? " checked" : '');
			$checked = "";
			if(!empty($schecked) || !empty($gchecked)) {
				$checked = " checked";
			}
			// $disabled = (!empty($sections[$section_id]) ? '' : '');
			$output .= "
				<div class='group_box'>
					<div class='group_header'>
						<label for='g". $group_id ."'>
							<input type='checkbox' name='group[". $section_id ."][". $group_id ."]' id='g". $group_id ."' data-section='". $section_id ."' value='". $group_id ."'". $checked ." onclick='check_all(this);'> ". $tmp['grp'] ."
						</label>
					</div>
					<div class='group_body' id='g". $group_id ."_body'>
			";
			foreach($tmp2 as $permission_id => $row) {
				$pchecked = (!empty($permissions[$permission_id]) ? " checked" : '');
				$checked = "";
				if(!empty($schecked) || !empty($gchecked) || $pchecked) {
					$checked = " checked";
				}
				$role_count = (!empty($row['role_title']) && substr_count($row['role_title'],",")) + 1;
				$roles = (!empty($row['role_title']) ? '- <a href="javascript:void(0);" title="'. $row['role_title'] .'" class="tooltip">'. $role_count .' Roles</a>' : '');

				// $disabled = (!empty($groups[$group_id]) ? '' : '');
				$output .= "
					<label for='p". $permission_id ."'>
						<input type='checkbox' name='permission[". $section_id ."][". $group_id ."][". $permission_id ."]' id='p". $permission_id ."' data-section='". $section_id ."' data-group='". $group_id ."'value='". $permission_id ."'". $checked ." onclick='uncheck_parent(this);'> ". $row['title']."
						". $roles ."
					</label><br>
				";
			}
			$output .= '
					</div>
				</div>
				
			';
		}
		$output .= '<div class="clear"></div></div><div class="clear"></div>';
	}
	echo $output;


?>

		<input type="submit" value="Update Information">
		<input type='hidden' name='id' value='<?php echo $id; ?>'>

	</form>
  </div>
<?php
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>

<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>

<?php
$js = trim(ob_get_clean());
if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>