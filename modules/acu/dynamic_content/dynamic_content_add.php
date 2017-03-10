<?php 
##################################################
#   Document Setup and Security
##################################################
_error_debug("MODULE: ". basename(__FILE__)); 	# Debugger 

if(!logged_in()) { safe_redirect("/login/"); }
if(!has_access('dynamic_content_add')) { BACK_REDIRECT(); }

post_queue($module_name,'modules/acu/dynamic_content/post_files/');

##################################################
#	Validation
##################################################

##################################################
#	DB Queries
##################################################
$q = "select id,title from public.dynamic_content_types where active";
$dct_res = db_query($q,"Getting Dynamic Content Types");

##################################################
#	Pre-Content
##################################################
$info = (!empty($_POST) ? $_POST : array());

library('directory_structure.php');
$templates = directory_list( $GLOBALS['root_path'] ."templates/" );

##################################################
#	Content
##################################################
?>
	<h2 class='dynamic-content'>Create Dynamic Content</h2>
  
  <div class='content_container'>
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
		<input required type="text" name="url" id="url" value="<?php if(!empty($info["url"])) { echo $info["url"]; } ?>">
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
		<textarea name="content" id="content"><?php if(!empty($info["content"])) { echo $info["content"]; } ?></textarea>
	</div>



	<p>
		<input type="submit" value="Create Dynamic Content">		
	</p>

	</form>
</div>
<?php
##################################################
#	Javascript Functions
##################################################
ob_start();
?>

<script type="text/javascript" src="/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

tinymce.init({
    selector: "textarea"
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
if(!empty($js)) { ADD_JS_CODE($js); }

##################################################
#	Additional PHP Functions
##################################################

##################################################
#	EOF
##################################################
?>