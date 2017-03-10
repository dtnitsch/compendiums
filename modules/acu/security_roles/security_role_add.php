<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("security_role_add")) { back_redirect(); }

post_queue($module_name,'modules/acu/security_roles/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
$q = "
	select
		security.permission.id
		,security.permission.title
		,security.permission.alias
		,security.permission.created
		,security.permission.modified
		,security.section.id as section_id
		,security.section.title as section
		,security.group.id as group_id
		,security.group.title as grp
	from security.permission
	join security.section on security.section.id = permission.section_id and security.section.active
	join security.group on security.group.id = permission.group_id and security.group.active
	where
		security.permission.active 
	order by
		section,
		grp,
		title
";
$res = db_query($q,'Getting Permission List');
##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());
add_css("security.css");
add_js("forms_advanced.js");
add_js("security.js");
##################################################
#	Content
##################################################
?>
	<h2 class='security-roles'>Create Security Role</h2>
  
  <div class='content_container'>
  
	<?php echo DUMP_MESSAGES(); ?>
  
	<form method="post" action="">
 
		<label class="form_label">Security Role <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="title" id="title" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>">
	</div>

	<label class="form_label">Alias <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="alias" id="alias" value="<?php if(!empty($info["alias"])) { echo $info["alias"]; } ?>">
	</div>

	<div class="inputs">
		<label for="description" class="form_label">Description</label><br>
		<textarea name="description" id="description"><?php if(!empty($info["description"])) { echo $info["description"]; } ?></textarea>
	</div>


<?php

	$output = '';
	$perms = array();
	while($row = db_fetch_row($res)) {
		$perms[$row['section_id']][$row['group_id']][$row['id']] = $row;
	}
	foreach($perms as $section_id => $tmp1) {
		$tmp = current(current($tmp1));
		$checked = (!empty($info['section'][$section_id]) ? " checked" : '');
		$output .= "
		<div class='section_header'>
			<div class='float_right'>
				<input type='button' value='All' onclick='checkbox_selection(\"s". $section_id ."_body\",\"all\")'>
				<input type='button' value='None' onclick='checkbox_selection(\"s". $section_id ."_body\",\"none\")'>
				<input type='button' value='Invert' onclick='checkbox_selection(\"s". $section_id ."_body\",\"invert\")'>
			</div>
			<label for='s". $section_id ."'>
				<input type='checkbox' name='section[". $section_id ."]' id='s". $section_id ."' value='". $section_id ."'". $checked ." onclick='toggle_permissions(this)'> ". $tmp['section'] ."
			</label>
		</div>
		<div class='section_body' id='s". $section_id ."_body'>
		";
		foreach($tmp1 as $group_id => $tmp2) {
			$tmp = current($tmp2);
			$checked = (!empty($info['group'][$group_id]) ? " checked" : '');
			$disabled = ($checked == '' ? ' disabled' : '');
			$output .= "
				<div class='group_box'>
					<div class='group_header'>
						<label for='g". $group_id ."'>
							<input ". $disabled ." type='checkbox' name='group[". $group_id ."]' id='g". $group_id ."' value='". $group_id ."'". $checked ." onclick='toggle_permissions(this)'> ". $tmp['grp'] ."
						</label>
					</div>
					<div class='group_body' id='g". $group_id ."_body'>
			";
			foreach($tmp2 as $permission_id => $row) {
				$checked = (!empty($info['permission'][$section_id][$group_id][$permission_id]) ? " checked" : '');
				$output .= "
					<label for='p". $permission_id ."'>
						<input ". $disabled ." type='checkbox' name='permission[". $section_id ."][". $group_id ."][". $permission_id ."]' id='p". $permission_id ."' value='". $permission_id ."'". $checked ."> ". $row['title'] ."
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

	<p>
		<input type="submit" value="Create Security Role">		
	</p>

	</form>
  
  </div>

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