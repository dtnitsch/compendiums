<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access("admin_users_edit")) { back_redirect(); }

post_queue($module_name,'modules/acu/users/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/users/');
}

$_SESSION['honeypot'] = "asdfa8f89asdnf8asdn";

##################################################
#	DB Queries
##################################################
library("security_functions.php");
library("users.php");

// $role_list = get_security_roles();
// $user_roles = get_user_security_roles($id);

$q = "select id,title from supplements.regions where active and country_id = 236 order by title";
$regions_res = db_query($q,"Getting Regions");

$q = "select id,title from supplements.countries where active";
$countries_res = db_query($q,"Getting Countries");

// Getting Address (if exists)
$q = "
	select
		i.id as institution_id
		,i.title
		,i.institution_type_id
		,i.city
		,i.address1
		,i.address2
		,i.postal_code
		,i.phone
		,r.\"2code\" as state
		,c.\"3code\" as country
	from public.institutions as i
	join supplements.regions as r on
		r.id = i.region_id
	join supplements.countries as c on
		c.id = i.country_id
	where
		i.active
		and i.id in (
			select
				s.institution_id
			from system.students as s
			join system.users as u on 
				u.id = s.user_id 
				and s.user_id = '". $id ."'
				and s.active
			where
				s.active
			group by
				institution_id
		)
    order by i.created
";
$addresses_res = db_query($q,"Getting Addresses");
$addresses = array();
while($row = db_fetch_row($addresses_res)) {
	$addresses[] = $row;
}


##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/users/");

library("validation.php");
add_js("validation.js");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = get_user_by_id($id);
	$info['institution'] = get_institution_by_user_id($id);
_error_debug("user info", $info);
}

##################################################
#	Content
##################################################
?>
	<h2 class='users'>Edit User: <?php echo $info["firstname"]." ".$info["lastname"]; ?></h2>

	<div class='content_container'>
  
		<?= user_navigation($id,"edit") ?>

		<div id="messages">
			<?= dump_messages() ?>
		</div>

		<form method="post" action="" onsubmit="return v.validate();"> <!-- return false; return v.validate() -->

		<div class='float_right' style='width: 50%;'>
			<label class="form_label" for="username">Username <span>*</span></label>
			<div class="form_data">
				<input required type="text" name="username" id="username" value="<?php if(!empty($info["username"])) { echo $info["username"]; } ?>">
			</div>

			<label class="form_label" for="pin">Pin <span>*</span></label>
			<div class="form_data">
				<input required type="text" name="pin" id="pin" value="<?php if(!empty($info["pin"])) { echo $info["pin"]; } ?>">
			</div>

			<?php
				$GLOBALS["show_password_acu"] = 1;
				$GLOBALS["user_edit_acu"] = 1;
				echo run_module("password");
			?>


			<label class="form_label">Security Roles <span>*</span></label>
			<div class="inputs">
				<input type="checkbox" name="is_superadmin" id="is_superadmin" value="1"<?php echo (!empty($info["is_superadmin"]) && $info["is_superadmin"] == "t" ? " checked" : ""); ?>>
				<label for="is_superadmin">Is Superadmin?</label>
			</div>
<?php
// $output = '';
// while($row = db_fetch_row($role_list)) {
// 	$checked = (!empty($user_roles[$row['id']]) ? ' checked' : '');
// 	$output .= '<br><label for="role_'. $row['id'] .'"><input type="checkbox" name="roles[]" id="role_'. $row['id'] .'" value="'. $row['id'] .'"'. $checked .'> '. $row['title'] .'</label>';
// }
// echo $output;

?>

			<label class="form_label">Addresses</label>
			<div class="form_text">
<?php
	if(empty($addresses)) {
		echo "No Addresses for this user";
	} else {
		// Check first for "parent" ID to use as primary
		$addy = array();
		foreach($addresses as $k => $row) {
			if($row['institution_type_id'] == 6) {
				$addy = $row;
				unset($addresses[$k]);
				break;
			}
		}

		if(empty($addy)) {
			// Take the first element from the array and use that
			$addy = array_shift($addresses);
		}
?>
				<div>
					<em>1. <?php echo $addy['title']; ?></em> <a href="javascript:void(0);" onclick="trigger_show_hide(this,'user_addresses_1')" class="glyphicons glyphicons-eye-open" title="Show/Hide Address"></a> <a href="/acu/schools/edit/?id=<?php echo $addy["institution_id"]; ?>" class="glyphicons glyphicons-pencil" style="margin: 0; padding: 0;" title="Edit Address"></a>
					<div id="user_addresses_1" style="padding-left: 10px; border: 1px solid #ccc;">
						<?php
							echo $address = trim($addy['address1'] ." ". $addy['address2']);
							if(empty($address)) {
								echo "<em>No Street Address</em>";
							}

							$address = "<br>". (empty($addy['city']) ? "<em>No City</em>" : $addy['city']);
							$address .= ", ". (empty($addy['state']) ? "<em>No State</em>" : $addy['state']);
							$address .= ", ". (empty($addy['postal_code']) ? "<em>No Postal Code</em>" : $addy['postal_code']);

							$address .= "<br>". (empty($addy['country']) ? "<em>No Country</em>" : $addy['country']);
							echo $address .= "<br>". (empty($addy['phone']) ? "<em>No Phone Number</em>" : $addy['phone']);
						?>
					</div>
				</div>
<?php
		foreach($addresses as $k => $row) {
?>
				<div class="mt">
					<em><?php echo ($k + 2).". ". $row['title']; ?></em> <a href="javascript:void(0);" onclick="trigger_show_hide(this,'user_addresses_<?php echo ($k + 2); ?>')" class="glyphicons glyphicons-eye-open" style="margin: 0; padding: 0;" title="Show/Hide Address"></a> 
					<a href="/acu/schools/edit/?id=<?php echo $row["institution_id"]; ?>" class="glyphicons glyphicons-pencil" style="margin: 0; padding: 0;" title="Edit Address"></a>
					<div id="user_addresses_<?php echo ($k + 2); ?>" style='display: none; padding-left: 10px; border: 1px solid #ccc;'>
						<?php
							echo $address = trim($row['address1'] ." ". $row['address2']);
							if(empty($address)) {
								echo "<em>No Street Address</em>";
							}

							$address = "<br>". (empty($row['city']) ? "<em>No City</em>" : $row['city']);
							$address .= ", ". (empty($row['state']) ? "<em>No State</em>" : $row['state']);
							$address .= ", ". (empty($row['postal_code']) ? "<em>No Postal Code</em>" : $row['postal_code']);

							$address .= "<br>". (empty($row['country']) ? "<em>No Country</em>" : $row['country']);
							echo $address .= "<br>". (empty($row['phone']) ? "<em>No Phone Number</em>" : $row['phone']);
						?>
					</div>
				</div>
<?php
		}

	} // end show addresses

?>

			</div>


		</div>


		<label class="form_label">Approved </label>
		<div class="form_data">
		<?php
		$yes_checked = (!empty($info['approved']) ? " checked" : '');
		$no_checked = (empty($info['approved']) ? " checked" : '');
		?>
			<label for="approved_yes"><input type="radio" name="approved" id="approved_yes" value="1"<?php echo $yes_checked; ?>> Yes</label>
			&nbsp;
			<label for="approved_no"><input type="radio" name="approved" id="approved_no" value="0"<?php echo $no_checked; ?>> No</label>
		</div>

		<label class="form_label" for="firstname">First Name <span>*</span></label>
		<div class="form_data">
			<input required type="text" name="firstname" id="firstname" placeholder="firstname" value="<?php if(!empty($info["firstname"])) { echo $info["firstname"]; } ?>">
		</div>

		<label class="form_label" for="lastname">Last Name <span>*</span></label>
		<div class="form_data">
			<input required type="text" name="lastname" id="lastname" value="<?php if(!empty($info["lastname"])) { echo $info["lastname"]; } ?>">
		</div>

		<label class="form_label" for="title">Title</label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>">
		</div>

		<label class="form_label" for="email">Email Address <span>*</span></label>
		<div class="form_data">
			<input required type="email" name="email" id="email" class="input_xlarge" placeholder="example@address.com" value="<?php if(!empty($info["email"])) { echo strtolower($info["email"]); } ?>">
		</div>

		<label class="form_label" for="phone1">Phone Number 1</label>
		<div class="form_data">
			<input type="phone1" name="phone1" id="phone1" placeholder="(555) 555 - 5555" value="<?php if(!empty($info["phone1"])) { echo $info["phone1"]; } ?>">
		</div>

		<label class="form_label" for="phone2">Phone Number 2</label>
		<div class="form_data">
			<input type="phone2" name="phone2" id="phone2" placeholder="(555) 555 - 5555" value="<?php if(!empty($info["phone2"])) { echo $info["phone2"]; } ?>">
		</div>

		<label class="form_label" for="phone3">Phone Number 3</label>
		<div class="form_data">
			<input type="phone3" name="phone3" id="phone3" placeholder="(555) 555 - 5555" value="<?php if(!empty($info["phone3"])) { echo $info["phone3"]; } ?>">
		</div>

		<label class="form_label" for="heard_about">Heard About</label>
		<div class="form_data">
			<span id="heard_about"><?php echo (empty($info["marketing_source"]) ? "Marketing Source Not Found" : $info["marketing_source"]); ?></span>
		</div>

		<label class="form_label" for="registration_type">Registration Type</label>
		<div class="form_data">
			<span id="registration_type"><?php echo (empty($info["registration_type_title"]) ? "Registration Type Not Found" : $info["registration_type_title"]); ?></span>
		</div>

		<label class="form_label" for="population">Population (Number of Students in Your Classroom)</label>
		<div class="form_data">
			<span id="population"><?php echo (empty($info["institution"]["population"]) ? "--" : $info["institution"]["population"]); ?></span>
		</div>

		<label class="form_label" for="other">Other</label>
		<div class="form_data">
			<textarea type="text" name="other" id="other" class="input_full"><?php if(!empty($info["other"])) { echo $info["other"]; } ?></textarea>
		</div>



		<p>
			<input type="submit" value="Update Information">
			<input type='hidden' name='id' value='<?php echo $id; ?>'>
			<input type='hidden' name='honeypot' id="honeypot" value='<?= $_SESSION['honeypot'] ?>'>

		</p>

		</form>

	</div>

<?php
	echo run_module("students", "modules/acu/students");
	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);
?>


<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>
<script type="text/javascript">
	var j = <?php echo validation_create_json_string(validation_load_file(__DIR__."/validation.json"),"js"); ?>;
	// name of variable should be sent in the validation function
	var v = new validation("v");
	v.load_json(j);

	// v.custom("email_db", function() {
	// 	return false;
	// },"JS EMail thingy failed");

	function trigger_show_hide(obj, id) {
		obj.className = (obj.className == 'glyphicons glyphicons-eye-open' ? 'glyphicons glyphicons-eye-close' : 'glyphicons glyphicons-eye-open');
		show_hide(id);
	}

	function show_acu_password() {
		document.getElementById("acu_password").innerHTML = "<label class=\"form_label\" for=\"password_acu1\">Admin Password <span>*</span></label><div class=\"form_data\"><input required type=\"password\" name=\"password_acu1\" id=\"password_acu1\" tabindex=\"52\"></div><label class=\"form_label\" for=\"password_acu2\">Admin Password Confirmation <span>*</span></label><div class=\"form_data\"><input required type=\"password\" name=\"password_acu2\" id=\"password_acu2\" tabindex=\"53\"></div><input type=\"hidden\" name=\"acu_password_reset\" value=\"true\">";
	}

</script>

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