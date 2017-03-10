<?php
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access('dynamic_content_edit')) { back_redirect(); }

post_queue($module_name,'modules/acu/dynamic_content/post_files/');

##################################################
#	Validation
##################################################
$id = get_page_id();
if(empty($id)) {
	warning_message("An error occured while trying to edit this record:  Missing Requred ID");
	safe_redirect('/acu/dynamic-content/');
}

##################################################
#	DB Queries
##################################################
$q = "select id,title from public.dynamic_content_types where active";
$dct_res = db_query($q,"Getting Dynamic Content Types");

##################################################
#	Pre-Content
##################################################
library("functions.php",$GLOBALS["root_path"] ."modules/acu/dynamic_content/");

$info = array();
if(!empty($_POST)) {
	$info = $_POST;
} else {
	$q = "
		select
			dynamic_content.*
			,system.paths.template
			,system.paths.path
		from public.dynamic_content
		left join system.paths on
			paths.dynamic_content_id = dynamic_content.id
			and dynamic_content_type_id = 1
		where dynamic_content.id='". $id ."'
	";
	$info = db_fetch($q,'Getting Dynamic Content');
}

library('directory_structure.php');
$templates = directory_list( $GLOBALS['root_path'] ."templates/" );

##################################################
#	Content
##################################################
?>
	<h2 class='dynamic-content'>Edit Dynamic Content: <?php echo $info["title"]; ?></h2>
  
  <div class='content_container'>

	<?= dynamic_content_navigation($id,"edit") ?>

	<?php echo dump_messages(); ?>

	<form method="post" action="">
 
	<div class="form_data">
		<label for="dynamic_content_type_id" class="form_label">Dynamic Content Type <span>*</span></label><br>
		<select required id="dynamic_content_type_id" name="dynamic_content_type_id">
			<option value="">-Select Dynamic Content Type-</option>
<?php
$output = '';
while($row = db_fetch_row($dct_res)) {
	$output .= '<option value="'. $row['id'] .'">'. $row['title'] .'</option>';
}
if(!empty($info['dynamic_content_type_id'])) {
	$output = str_replace('value="'. $info['dynamic_content_type_id'] .'"', 'value="'. $info['dynamic_content_type_id'] .'" selected',$output);
}
echo $output;
?>
		</select>
	</div>

	<label class="form_label">Dynamic Content <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="title" id="title" value="<?php if(!empty($info["title"])) { echo $info["title"]; } ?>" onkeyup='convert_to_slug(this.value)' onchange='convert_to_slug(this.value)'>
	</div>

	<label class="form_label">Alias <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="alias" id="alias" value="<?php if(!empty($info["alias"])) { echo $info["alias"]; } ?>">
	</div>

	<label class="form_label">URL <span>*</span></label>
	<div class="form_data">
		<input required type="text" name="path" id="path" value="<?php if(!empty($info["path"])) { echo $info["path"]; } ?>">
	</div>


	<div class="form_data">
		<label for="template" class="form_label">Template <span>*</span></label><br>
		<select required id="template" name="template">
			<option value="">-Select Template-</option>
<?php
$output = select_files_and_folders( $templates, '/templates/' );
if(!empty($info['template'])) {
	$file = '/templates/' . $info['template'] .'.template.php';
	$output = str_replace('value="'. $file .'"', 'value="'. $file .'" selected',$output);
}
echo $output;
?>
		</select>
	</div>

	<div class="form_data">
		<label for="content" class="form_label">Content</label><br>
		<textarea name="content" id="content" class="mceEditor"><?php if(!empty($info["content"])) { echo base64_decode($info["content"]); } ?></textarea>
	</div>


	<p>
		<input type="submit" value="Update Information">		
		<input type='hidden' name='id' value='<?php echo $id; ?>'>
	</p>

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

<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
    // selector: "textarea"
    mode : "specific_textareas"
    ,editor_selector : "mceEditor"
    ,plugins: [
    	"code",
        "link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste textcolor colorpicker"
    ]
    ,toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor | code | preview"
    ,menubar: false
    ,allow_script_urls: true
	,relative_urls: false
	,convert_urls: false
});

function convert_to_slug(val) {
	var val = val.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-');
    $id('url').value = '/'+ val +'/';
    $id('alias').value = val;
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