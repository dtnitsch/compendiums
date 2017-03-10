<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger

// if(!logged_in()) { safe_redirect("/login/"); }
// if(!has_access("list_edit")) { back_redirect(); }

post_queue($module_name,'modules/lists/post_files/');

##################################################
#	Validation
##################################################
$id = get_url_param("key");
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/lists/');
}

##################################################
#	DB Queries
##################################################

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/lists/");

// library("validation.php");
// add_js("validation.js");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$info = db_fetch("select * from public.list where key='". $id ."'",'Getting List');
	$q = "
		select public.asset.*
		from public.asset
		join public.list_asset_map on 
			list_asset_map.asset_id = asset.id
			and list_asset_map.list_id = '". $info['id'] ."'
		order by
			asset.title
	";
	$assets = db_query($q,"Getting assets");
}

##################################################
#	Content
##################################################
?>
	<h2 class='lists'>Edit List: <?php echo $info['title']; ?></h2>
  
  	<?php echo dump_messages(); ?>
	<form id="addform" method="post" action="">

		<label class="form_label" for="title">List Name <span>*</span></label>
		<div class="form_data">
			<input type="text" name="title" id="title" value="<?php echo $info['title']; ?>">
		</div>

		<label class="form_label">Visibility</label>
		<div class="form_data">
			<label for="public"><input type="radio" name="visibility" id="public" value="public"> Public</label>
			<label for="private"><input type="radio" name="visibility" id="private" value="private"> Private</label>
		</div>

		<label class="form_label" for="title">Inputs</label>
		<div class="form_data">
<?php
$list = "";
while($row = db_fetch_row($assets)) {
	$list .= $row['title'] ."\n";
}
$list = substr($list,0,-1);

?>		
			<textarea name="inputs" id="inputs" style="width: 400px; height: 150px;"><?php echo $list; ?></textarea>
			<div style="font-size: 80%;">*Notes: Tab Deliminated List - Name &nbsp; Percentage &nbsp; Tags</div>
		</div>

		<label class="form_label" for="title">Input Options</label>
		<div class="form_data">
			<label for="percentages">
				<input type="checkbox" name="options" id="percentages" value="percentages"> Percentages
			</label>
			&nbsp;
			<label for="tags">
				<input type="checkbox" name="options" id="tags" value="tags"> Tags
			</label>
		</div>
		

			<!--input checked type="radio" name="multipart" value="yes"> Individual
			<input type="radio" name="multipart" value="no"> Multi-Part -->

		<!--input type="button" value="Add List" onclick="addform()"-->
		<input type="submit" value="Edit List">
	</form>

<?php

	site_wide_notes('ajax',$GLOBALS['project_info']['path_data']['id'],$id);


##################################################
#	Javascript Functions
##################################################
// ob_start();
// ?><?php
// $js = trim(ob_get_clean());
// if(!empty($js)) { add_js_code($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
