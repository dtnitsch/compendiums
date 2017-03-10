<?php

//include_once($_SERVER['DOCUMENT_ROOT']."/lib/info-uploads.php");

function proc_uploads($id, $module, $is_edit = false) {
	global $session;

	if($is_edit) {
		$q = "
			UPDATE 
				".db_tables('uploads_list')." 
			SET 
				deleted = 1
			WHERE 
				mod_id = '".$id."' 
			AND 
				UPPER(type) = '".strtoupper($module)."' 
		";

		db_query($q);

	}

	if(isset($_POST['upl_id'])){
		foreach($_POST['upl_id'] as $k => $v) { 

			$response = add_uploads_list($id, $_POST['upl_id'][$k], $module, $k=false);

			if($response){

				// active uploads
				$q = "
					UPDATE 
						".db_tables('uploads')." 
					SET 
						active = 1
					WHERE 
						id = '".$_POST['upl_id'][$k]."' 
				";

				db_query($q);

			}

		}
	}


	// clear uploads

	$resposne = clear_uploads();

	if($resposne){
		return true;
	}

	return false;

}

function add_uploads_list($upl_id, $type_id, $type, $sortorder=false){

	$q = "
		INSERT 
		INTO 
			".db_tables('uploads_list')." 
		(
			upl_id
			,type_id
			,type
			,sortorder
		) 
		VALUES 
		(
		 	'".db_prep_sql($upl_id)."'
			,'".db_prep_sql($type_id)."'
			,'".db_prep_sql(strtoupper($type))."'
			,'".db_prep_sql($sortorder)."'
		)
	";

	//echo $q;
	
	return db_query($q);

}

function update_uploads_list($id, $field, $value){

	$q = "
		UPDATE 
			".db_tables('uploads_list')." 
		SET 
			".$field." = '".db_prep_sql($value)."' 
		WHERE 
			upl_id = '".$id."' 
	";

	return db_query($q);

}

function clear_uploads(){
	global $session;

	$q = "
		SELECT 
			id
			,filename
			,ext 
		FROM 
			".db_tables('uploads')." 
		WHERE 
			status = 0 
		AND 
			user_id = '".$session->user['id']."' 
	";

	$res = db_query($q);

	if($res){

		while($row = db_fetch_row($res)) {

			@unlink($GLOBALS['MAINURL']."upl/".strtolower($row['type'])."/".$row['filename'].".".$row['ext']);

			db_query("UPDATE ".db_tables('uploads')." SET deleted = 1 WHERE id = '".$row['id']."'");			

		}

		return true;
	}

	return false;
}

function delete_uploads($id){
	
	$q = "
		DELETE 
		FROM 
			".db_tables('uploads')." 
		WHERE 
			id = '".$id."' 
	";

	return db_query($q);
}

function delete_uploads_list($field, $value, $module = false){
	global $database;

	$q = "
		DELETE 
		FROM 
			".db_tables('uploads_list')." 
		WHERE 
			".$field." = '".$value."' 
	";

	if($module)
		$q .= "AND 
				UPPER(type) = '".strtoupper($module)."' ";

	return db_query($q);
}

?>