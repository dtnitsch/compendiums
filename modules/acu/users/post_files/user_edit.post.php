<?php
if(!empty($_POST) && !error_message()) {
	library("validation.php");
	library("membership.php");

	// define("HONEYPOT","asdfadsofjas32940d");
	// $_POST["firstname"] = "";
	// $_POST["password1"] = "1";

	// validation_custom("email_db",function() {
	// 	return false;
	// },"Email DB failed");

	$json = validation_create_json_string(validation_load_file(__DIR__ ."/../validation.json"),"php");
	validate_from_json($json);

	error_message(get_all_validation_errors());
	_error_debug("all validate errors",get_all_validation_errors());

	if(!error_message()) {

		$table_info = array(
			"table_name" => "users"
			,"primary_key" => "id"
			# optional
			,"audit_table" => "system_table_logs"
			,"primary_key_value" => db_prep_sql($_POST["id"])
			# Key = DB column Name, Value = Post name
			,"table_columns" => array()
		);
		if(uses_schema()) {
			$table_info["table_schema"] = "system";
			$table_info["audit_schema"] = "audits";
			$table_info["audit_table"] = "system_table_logs";
		}

		$is_superadmin = 'f';
		if(!empty($_POST["is_superadmin"])) {
			$is_superadmin = ($_POST["is_superadmin"] == "1" || strtolower($_POST["is_superadmin"]) == 't' ? 't' : 'f');
		}
		$_POST['is_superadmin'] = $is_superadmin;

		$arr = array(
			"firstname" => db_prep_sql($_POST["firstname"])
			,"lastname" => db_prep_sql($_POST["lastname"])
			,"email" => db_prep_sql(strtolower($_POST["email"]))
			,"is_superadmin" => $is_superadmin
			,"phone1" => db_prep_sql(format_phone_number($_POST['phone1']))
			,"phone2" => (empty($_POST['phone2']) ? "" : db_prep_sql(format_phone_number($_POST['phone2'])))
			,"phone3" => (empty($_POST['phone3']) ? "" : db_prep_sql(format_phone_number($_POST['phone3'])))
			,"title" => (empty($_POST['title']) ? "" : db_prep_sql($_POST['title']))
			,"username" => db_prep_sql($_POST['username'])
			,"pin" => db_prep_sql($_POST['pin'])
			,"other" => db_prep_sql($_POST['other'])
		);
		if(!empty($_POST["password1"])) {
			list($password,$password_salt) = user_hash_passwords($_POST["password1"]);
			$arr["password"] = db_prep_sql($password);
			$arr["password_salt"] = db_prep_sql($password_salt,(uses_schema() ? "bytea" : ""));
		}
		if(!empty($_POST["password_acu1"])) {
			list($password_acu,$password_salt_acu) = user_hash_passwords($_POST["password_acu1"]);
			$arr["password_acu"] = db_prep_sql($password_acu);
			$arr["password_salt_acu"] = db_prep_sql($password_salt_acu,(uses_schema() ? "bytea" : ""));
		}

		$table_info["table_columns"][] = $arr;

		$res = "";
		if(($original_values = post_has_changes($table_info)) !== false) {
			$table_info["original_values"] = $original_values;

			if(($res = post_functions_update($table_info)) === false) {
				error_message("An error has occured trying to update this record");
			}
		} else {
			error_message("Comparison table is incorrect");
		}

		if(!error_message()) {

			$table = "security_role_user_map";
			if(uses_schema()) {
				$table = '"security"."role_user_map"';
			}

			// Has roles
			if(!empty($_POST["roles"])) {
				$res = db_query("select role_id from ". $table ." where user_id = '". $_POST['id'] ."'");
				$db_roles = array();
				while($row = db_fetch_row($res)) {
					$db_roles[] = $row["role_id"];
				}

				$post_roles = array();
				foreach($_POST["roles"] as $role_id) {
					$post_roles[] = $role_id;
				}

				$add = array_diff($post_roles,$db_roles);
				$del = array_diff($db_roles,$post_roles);

				if(!empty($add) || !empty($del)) {
					$table = "security_role_user_map";
					if(uses_schema()) {
						$table = '"security"."role_user_map"';
					}
					if(!empty($del)) {

						$q = "delete from ". $table ." where user_id='". $_POST['id'] ."' and role_id in (". implode(',',$del) .")";
						db_query($q,"Deleting security roles");
					}
					if(!empty($add)) {
						$q = "";
						foreach($add as $role_id) {
							$q .= "('". $_POST['id'] ."','". $role_id ."',now(),now()),";
						}
						$q = "insert into ". $table ." (user_id,role_id,created,modified) values ". substr($q,0,-1);
						db_query($q,"Inserting security roles");
					}

					$table_info["additional_fields"]["role_id"]["old"] = json_encode($db_roles);
					$table_info["additional_fields"]["role_id"]["new"] = json_encode($post_roles);
				}
			} else {
				// If empty, remove all security roles
				$q = "delete from ". $table ." where user_id = '". $_POST['id'] ."'";
				db_query($q,"Deleting all security roles");

			}
		}

		if(!error_message() && $res != "error") {
			audit("table_update",$table_info);

			$redirection_path = "/acu/users/";
			set_post_message("The record has been successfully updated");
			set_safe_redirect($redirection_path);

		} else {
			error_message("An error has occurred while trying to update this record");
		}
	}
}

function format_phone_number($number) {
	$number =  preg_replace('/[^0-9]/','',trim($number));
	if(is_numeric($number)) {
		$number = (string)$number;
		$len = strlen($number);
		if($len == 7) {
			return substr($number, 0, 3) .' - '. substr($number, 6);
		} else if($len == 10) {
			return '('. substr($number, 0, 3) .') '. substr($number, 3, 3) .' - '. substr($number, 6);
		} else if($len == 11) {
			return substr($number, 0, 1) .' ('. substr($number, 1, 3) .') '. substr($number, 4, 3) .' - '. substr($number, 7);
		}
		return $number;
	}
	return "<i>".$number."</i>";
}
