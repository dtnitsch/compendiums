<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_role_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/security_roles/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/security-roles/');
}

##################################################
#	DB Queries
##################################################
$q = "
	select
		security.permissions.id
		,security.permissions.title
		,security.permissions.alias
		,security.permissions.created
		,security.permissions.modified
		,security.sections.id as section_id
		,security.sections.title as section
		,security.groups.id as group_id
		,security.groups.title as grp
		,security.role_permission_map.section_id as has_section
		,security.role_permission_map.group_id as has_group
		,security.role_permission_map.permission_id as has_permission
	from security.permissions
	join security.sections on security.sections.id = permissions.section_id and security.sections.active
	join security.groups on security.groups.id = permissions.group_id and security.groups.active
	left join security.role_permission_map on
		(
			role_permission_map.section_id = permissions.section_id
			or role_permission_map.group_id = permissions.group_id
			or role_permission_map.permission_id = permissions.id
		)
		and role_permission_map.role_id = '". $id ."'
	where
		security.permissions.active 
	order by
		section,
		grp,
		title
";
$res = db_query($q,'Getting Permissions List');

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/security_roles/");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_fetch("select * from security.roles where id='". $id ."'",'Getting Security Role');
}

add_css("security.css");
add_js("forms_advanced.js");
add_js("security.js");
##################################################
#	Content
##################################################
?>
	<h2 class='security-roles'>Edit Security Role: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= security_role_navigation($id,"edit") ?>

	<?= dump_messages() ?>


	<form method="post" action="">
 
		<label class="form_label">Security Role <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="title" id="title" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>">
	</div>

	<label class="form_label">Alias <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="alias" id="alias" value="<?php if(!empty($info["alias"])) { echo $info["alias"]; } ?>">
	</div>

		<label for="description" class="form_label">Description</label>
    <div class="form_data">
		<textarea name="description" id="description"><?php if(!empty($info["description"])) { echo $info["description"]; } ?></textarea>
</div>

<?php

	$output = '';
	$perms = array();
	$sections = array();
	$groups = array();
	$permissions = array();
	while($row = db_fetch_row($res)) {
		$perms[$row['section_id']][$row['group_id']][$row['id']] = $row;
		if(!empty($row['has_section'])) {
			$sections[$row['has_section']] = 1;
		}
		if(!empty($row['has_group'])) {
			$groups[$row['has_group']] = 1;
		}
		if(!empty($row['has_permission'])) {
			$permissions[$row['has_permission']] = 1;
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
				// $disabled = (!empty($groups[$group_id]) ? '' : '');
				$output .= "
					<label for='p". $permission_id ."'>
						<input type='checkbox' name='permission[". $section_id ."][". $group_id ."][". $permission_id ."]' id='p". $permission_id ."' data-section='". $section_id ."' data-group='". $group_id ."'value='". $permission_id ."'". $checked ." onclick='uncheck_parent(this);'> ". $row['title']."
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
/*
ob_start();
?>
<script type="text/javascript"></script>
<?php
$js = trim(ob_get_clean());
if(!empty($js)) { ADD_JS_CODE($js); }
*/

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>